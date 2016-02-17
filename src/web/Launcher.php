<?php
namespace groupcash\bank\web;

use groupcash\bank\AddBacker;
use groupcash\bank\app\ApplicationService;
use groupcash\bank\app\McryptCryptography;
use groupcash\bank\app\PersistentEventStore;
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

    /** @var Groupcash */
    private $lib;

    /** @var ApplicationService */
    private $service;

    public function __construct($rootDir) {
        $this->lib = new Groupcash(new EccKeyService());
        $this->service = new ApplicationService(
            new PersistentEventStore($rootDir . '/user/data'),
            new McryptCryptography(),
            $this->lib,
            $this->getSecret($rootDir . '/user/secret'));
    }

    private function getSecret($file) {
        if (!file_exists($file)) {
            mkdir(dirname($file), 0777, true);
            $secret = base64_encode(openssl_random_pseudo_bytes(128));
            file_put_contents($file, $secret);
        } else {
            $secret = file_get_contents($file);
        }
        return $secret;
    }

    public function run() {
        WebDelivery::quickResponse(IndexResource::class,
            WebApplication::init(function (WebApplication $domin) {
                $this->registerActions($domin);
            }, WebDelivery::init()));
    }

    private function registerActions(WebApplication $domin) {
        $this->addCommand($domin, CreateAccount::class);
        $this->addCommand($domin, AuthorizeIssuer::class);
        $this->addCommand($domin, AddBacker::class);
        $this->addCommand($domin, DeclarePromise::class);
        $this->addCommand($domin, IssueCoins::class);
        $this->addCommand($domin, SendCoins::class);
        $this->addQuery($domin, ListTransactions::class);
    }

    private function addCommand(WebApplication $domin, $action) {
        return $this->addAction($domin, $action, function ($action) {
            return $this->service->handle($action);
        });
    }

    private function addQuery(WebApplication $domin, $action) {
        return $this->addAction($domin, $action, function ($action) {
            return $this->service->execute($action);
        });
    }

    private function addAction(WebApplication $domin, $action, $handle) {
        $objectAction = new GenericObjectAction($action, $domin->types, $domin->parser, $handle);
        $domin->actions->add((new \ReflectionClass($action))->getShortName(), $objectAction);

        return $objectAction->generic();
    }
}