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
use groupcash\bank\SendCoins;
use groupcash\php\Groupcash;
use groupcash\php\impl\EccKeyService;
use rtens\domin\delivery\web\adapters\curir\root\IndexResource;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\fields\AdapterField;
use rtens\domin\delivery\web\fields\ObjectField;
use rtens\domin\delivery\web\renderers\dashboard\types\Panel;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\Parameter;
use rtens\domin\parameters\File;
use rtens\domin\reflection\GenericObjectAction;
use watoki\curir\WebDelivery;
use watoki\reflect\type\ClassType;

class Launcher {

    /** @var Application */
    private $app;

    public function __construct($rootDir) {
        $this->app = new Application(
            new PersistentEventStore($rootDir . '/user/data'),
            new McryptCryptography(),
            new Groupcash(new EccKeyService()),
            new PersistentVault(new OpenSslRandomNumberGenerator(), $rootDir . '/user'));
    }

    public function run() {
        WebDelivery::quickResponse(IndexResource::class,
            WebApplication::init(function (WebApplication $domin) {
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
        $this->addAction($domin, ListTransactions::class);
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
        $domin->fields->add((new AdapterField(new ObjectField($domin->types, $domin->fields)))
            ->setHandles(function (Parameter $parameter) {
                return $parameter->getType() == new ClassType(Authentication::class);
            })
            ->setTransformParameter(function (Parameter $parameter) {
                return $parameter->withType(new ClassType(FileAuthentication::class));
            })
            ->setBeforeRender(function (Authentication $authentication = null) {
                if (!$authentication) {
                    return null;
                }
                return new FileAuthentication($authentication->getKey(), $authentication->getPassword());
            })
            ->setAfterInflate(function (FileAuthentication $authentication) {
                $key = $authentication->getKey();
                if ($key instanceof File) {
                    $key = $key->getContent();
                }
                return new Authentication($key, $authentication->getPassword());
            }));
        $domin->fields->add(new PasswordField());
    }
}