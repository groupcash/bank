<?php
namespace groupcash\bank\web;

use groupcash\bank\AddBacker;
use groupcash\bank\app\Application;
use groupcash\bank\app\McryptCryptography;
use groupcash\bank\app\OpenSslRandomNumberGenerator;
use groupcash\bank\app\PersistentVault;
use groupcash\bank\app\sourced\store\PersistentEventStore;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\CreateAccount;
use groupcash\bank\DeclarePromise;
use groupcash\bank\IssueCoins;
use groupcash\bank\ListTransactions;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\RandomNumberGenerator;
use groupcash\bank\SendCoins;
use groupcash\php\Groupcash;
use groupcash\php\impl\EccKeyService;
use rtens\domin\delivery\web\adapters\curir\root\IndexResource;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\menu\ActionMenuItem;
use rtens\domin\delivery\web\renderers\dashboard\types\Panel;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\execution\RedirectResult;
use rtens\domin\parameters\File;
use rtens\domin\reflection\GenericMethodAction;
use rtens\domin\reflection\GenericObjectAction;
use watoki\curir\cookie\Cookie;
use watoki\curir\cookie\CookieStore;
use watoki\curir\WebDelivery;
use watoki\stores\file\raw\RawFileStore;

class Launcher {

    const SESSION_COOKIE = 'groupcash_session';

    /** @var Groupcash */
    private $lib;

    /** @var RandomNumberGenerator */
    private $random;

    /** @var Application */
    private $app;

    /** @var CookieStore */
    private $cookies;

    /** @var RawFileStore */
    private $session;

    /** @var Authenticator */
    private $authenticator;

    public function __construct($rootDir) {
        $this->random = new OpenSslRandomNumberGenerator();
        $vault = new PersistentVault($this->random, $rootDir . '/user');
        $crypto = new McryptCryptography();

        $this->authenticator = new Authenticator($crypto, $vault);

        $this->lib = new Groupcash(new EccKeyService());
        $this->app = new Application(
            new PersistentEventStore($rootDir . '/user/data'),
            $crypto,
            $this->lib,
            $vault);

        $this->session = new RawFileStore($rootDir . '/user/sessions');
    }

    public function run() {
        WebDelivery::quickResponse(IndexResource::class,
            WebApplication::init(function (WebApplication $domin) {
                $this->cookies = $domin->factory->getInstance(CookieStore::class);

                $domin->setNameAndBrand('bank');
                $this->registerActions($domin);
                $this->registerFields($domin);
            }, WebDelivery::init()));
    }

    private function registerActions(WebApplication $domin) {
        $this->addAction($domin, CreateAccount::class)
            ->setAfterExecute(function ($keys) use ($domin) {
                $keyPanel = function ($heading, $content) {
                    return new Panel($heading, new Element('div', [], [
                        new Element('textarea', [
                            'class' => 'form-control',
                            'onclick' => 'this.select();'
                        ], [
                            $content
                        ]),
                        new Element('a', [
                            'class' => 'btn btn-success',
                            'download' => str_replace(' ', '_', strtolower($heading)) . '_' . substr(md5($content), -6),
                            'href' => 'data:text/plain;base64,' . base64_encode($content),
                            'target' => '_blank'
                        ], [
                            'Save as File'
                        ])
                    ]));
                };

                return [
                    $keyPanel('Private Key', $keys['key']),
                    $keyPanel('Public Address', $keys['address'])
                ];
            });
        $this->addAction($domin, AuthorizeIssuer::class);
        $this->addAction($domin, AddBacker::class);
        $this->addAction($domin, DeclarePromise::class);
        $this->addAction($domin, IssueCoins::class);
        $this->addAction($domin, SendCoins::class);
        $this->addAction($domin, ListTransactions::class)
            ->setModifying(false);
        $this->registerSessionManagement($domin);
    }

    public function startSession(Authentication $authentication) {
        $key = $this->authenticator->getKey($authentication);
        $this->lib->getAddress($key);

        $sessionKey = md5($this->random->generate());
        $sessionPassword = $this->random->generate();

        $encryptedKey = $this->authenticator->encrypt($key, $sessionPassword);

        $this->cookies->create(new Cookie([
            'key' => $encryptedKey,
            'session' => $sessionKey
        ]), self::SESSION_COOKIE);

        $this->session->create(new \watoki\stores\file\raw\File($sessionPassword), $sessionKey);

        return new RedirectResult('.');
    }

    public function endSession() {
        if ($this->cookies->hasKey(self::SESSION_COOKIE)) {
            /** @var Cookie $cookie */
            $cookie = $this->cookies->read(self::SESSION_COOKIE);

            $this->session->delete($cookie->payload['session']);
            $this->cookies->delete(self::SESSION_COOKIE);
        }

        return new RedirectResult('.');
    }

    private function addAction(WebApplication $domin, $action) {
        $handle = function ($action) {
            return $this->app->handle($action);
        };

        $objectAction = new GenericObjectAction($action, $domin->types, $domin->parser, $handle);
        $domin->actions->add((new \ReflectionClass($action))->getShortName(), $objectAction);

        return $objectAction->generic();
    }

    private function registerFields(WebApplication $domin) {
        $domin->fields->add(new PasswordField());
        $domin->fields->add(new AuthenticationField($domin->types, $domin->fields, $this->getSessionAuthentication()));
    }

    private function getSessionAuthentication() {
        if ($this->cookies->hasKey(self::SESSION_COOKIE)) {
            /** @var Cookie $cookie */
            $cookie = $this->cookies->read(self::SESSION_COOKIE);
            $sessionPassword = $this->session->read($cookie->payload['session'])->getContents();

            return new Authentication($cookie->payload['key'], $sessionPassword);
        } else {
            return null;
        }
    }

    private function registerSessionManagement(WebApplication $domin) {
        if (!$this->cookies->hasKey(self::SESSION_COOKIE)) {
            $domin->actions->add('startSession',
                (new GenericMethodAction($this, 'startSession', $domin->types, $domin->parser))
                    ->generic()
                    ->setCaption('Start Session'));
            $domin->menu->addRight(new ActionMenuItem('Login', 'startSession'));
        } else {
            $domin->actions->add('endSession',
                (new GenericMethodAction($this, 'endSession', $domin->types, $domin->parser))
                    ->generic()
                    ->setCaption('End Session'));
            $domin->menu->addRight(new ActionMenuItem('Logout', 'endSession'));
        }
    }
}