<?php
namespace groupcash\bank\app\web;

use groupcash\bank\app\Application;
use groupcash\bank\app\crypto\McryptCryptography;
use groupcash\bank\app\sourced\stores\StoringEventStore;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\CreateAccount;
use groupcash\bank\CreateBacker;
use groupcash\bank\EstablishCurrency;
use groupcash\bank\IssueCoin;
use groupcash\bank\SendCoins;
use groupcash\php\algorithms\EccAlgorithm;
use groupcash\php\Groupcash;
use rtens\domin\delivery\web\adapters\curir\root\IndexResource;
use rtens\domin\delivery\web\WebApplication;
use rtens\domin\reflection\GenericObjectAction;
use watoki\curir\WebDelivery;
use watoki\stores\serializing\SerializerRepository;
use watoki\stores\serializing\serializers\JsonSerializer;
use watoki\stores\stores\FileStore;

class Launcher {

    /** @var string */
    private $userDir;

    /**
     * @param string $userDir
     */
    public function __construct($userDir) {
        SerializerRepository::setDefault((new JsonSerializer())->setPrettyPrint());

        $this->userDir = $userDir;
        $this->application = new Application(
            new StoringEventStore(new FileStore($userDir . '/data/events')),
            new Groupcash(new EccAlgorithm()),
            new McryptCryptography());
    }

    public function run() {
        WebDelivery::quickResponse(IndexResource::class, WebApplication::init(function (WebApplication $app) {
            $app->setNameAndBrand('bank');

            $app->fields->add(new BinaryField());

            $this->addAction($app, CreateAccount::class);
            $this->addAction($app, CreateBacker::class);
            $this->addAction($app, EstablishCurrency::class);
            $this->addAction($app, AuthorizeIssuer::class);
            $this->addAction($app, IssueCoin::class);
            $this->addAction($app, SendCoins::class);
        }, WebDelivery::init()));
    }

    private function addAction(WebApplication $app, $class) {
        $app->actions->add((new \ReflectionClass($class))->getShortName(), new GenericObjectAction($class, $app->types, $app->parser, function ($action) {
            return $this->application->handle($action);
        }));
    }
}