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
use groupcash\bank\SendCoins;
use groupcash\php\Groupcash;
use groupcash\php\impl\EccKeyService;
use rtens\domin\delivery\web\adapters\curir\root\IndexResource;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\reflection\GenericObjectAction;
use watoki\curir\WebDelivery;

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
            }, WebDelivery::init()));
    }

    private function registerActions(WebApplication $domin) {
        $this->addAction($domin, CreateAccount::class);
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
}