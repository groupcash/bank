<?php
namespace groupcash\bank\web;

use groupcash\bank\AddBacker;
use groupcash\bank\AddExistingBacker;
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
use groupcash\bank\projecting\TransactionHistory;
use groupcash\bank\RegisterCurrency;
use groupcash\bank\SendCoins;
use groupcash\bank\web\fields\AccountIdentifierField;
use groupcash\bank\web\fields\AddressCodeField;
use groupcash\bank\web\fields\AuthenticationField;
use groupcash\bank\web\fields\BackerIdentifierField;
use groupcash\bank\web\fields\CurrencyIdentifierField;
use groupcash\bank\web\fields\FractionField;
use groupcash\bank\web\fields\IdentifierField;
use groupcash\bank\web\fields\PasswordField;
use groupcash\bank\web\renderers\CoinsRenderer;
use groupcash\bank\web\renderers\CreatedAccountRenderer;
use groupcash\bank\web\renderers\CurrencyRenderer;
use groupcash\bank\WithdrawCoins;
use groupcash\php\Groupcash;
use groupcash\php\impl\EccKeyService;
use rtens\domin\delivery\web\adapters\curir\root\IndexResource;
use rtens\domin\delivery\web\menu\ActionMenuItem;
use rtens\domin\delivery\web\renderers\dashboard\types\Panel;
use rtens\domin\delivery\web\renderers\tables\types\ObjectTable;
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

    /** @var string */
    private $baseUrl;

    public function __construct($rootDir, $baseUrl) {
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
        $this->baseUrl = $baseUrl;
    }

    public function run() {
        WebDelivery::quickResponse(IndexResource::class,
            WebApplication::init(function (WebApplication $domin) {
                $this->cookies = $domin->factory->getInstance(CookieStore::class);

                $domin->setNameAndBrand('bank');
                $this->registerActions($domin);
                $this->registerFields($domin);
                $this->registerRenderers($domin);
            }, WebDelivery::init()));
    }

    private function registerActions(WebApplication $domin) {
        $this->addAction($domin, CreateAccount::class);
        $this->addAction($domin, RegisterCurrency::class);
        $this->addAction($domin, AuthorizeIssuer::class);
        $this->addAction($domin, AddBacker::class);
        $this->addAction($domin, AddExistingBacker::class);
        $this->addAction($domin, DeclarePromise::class);
        $this->addAction($domin, IssueCoins::class);
        $this->addAction($domin, WithdrawCoins::class);
        $this->addAction($domin, SendCoins::class);
        $this->addAction($domin, ListTransactions::class)
            ->setModifying(false)
            ->setAfterExecute(function (TransactionHistory $history) use ($domin) {
                return [
                    new Panel('Total', $history->getTotal()),
                    new Panel('Transactions',
                        (new ObjectTable($history->getTransactions(), $domin->types))
                            ->selectProperties(['when', 'subject', 'currency', 'amount']))
                ];
            });
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
        $domin->fields->add(new FractionField());
        $domin->fields->add(new AuthenticationField($domin->types, $domin->fields, $this->getSessionAuthentication()));
        $domin->fields->add(new CurrencyIdentifierField($domin->fields, $this->app));
        $domin->fields->add(new BackerIdentifierField($domin->fields, $this->app));
        $domin->fields->add(new AccountIdentifierField($domin->fields));
        $domin->fields->add(new IdentifierField());
        $domin->fields->add(new AddressCodeField());
    }

    private function registerRenderers(WebApplication $domin) {
        $domin->renderers->add(new CreatedAccountRenderer($domin->renderers, $this->baseUrl . '/SendCoins'));
        $domin->renderers->add(new CurrencyRenderer());
        $domin->renderers->add(new CoinsRenderer());
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