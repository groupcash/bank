<?php
namespace groupcash\bank\app\io\web;

use groupcash\bank\app\Application;
use groupcash\bank\app\crypto\McryptCryptography;
use groupcash\bank\app\io\BinaryTransformer;
use groupcash\bank\app\io\IdentifierTransformer;
use groupcash\bank\app\sourced\stores\StoringEventStore;
use groupcash\bank\ApproveRequest;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\CancelRequest;
use groupcash\bank\GenerateAccount;
use groupcash\bank\CreateBacker;
use groupcash\bank\EstablishCurrency;
use groupcash\bank\IssueCoin;
use groupcash\bank\RegisterBacker;
use groupcash\bank\RequestCoins;
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
use watoki\stores\transforming\TransformerRegistryRepository;

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
        $this->configureStores();

        WebDelivery::quickResponse(IndexResource::class, WebApplication::init(function (WebApplication $app) {
            $app->setNameAndBrand('bank');

            $app->fields->add(new BinaryField());
            $app->fields->add(new IdentifierField());
            $app->fields->add(new FractionField());

            $this->addAction($app, GenerateAccount::class);
            $this->addAction($app, RegisterBacker::class);
            $this->addAction($app, EstablishCurrency::class);
            $this->addAction($app, AuthorizeIssuer::class);
            $this->addAction($app, CreateBacker::class);
            $this->addAction($app, IssueCoin::class);
            $this->addAction($app, SendCoins::class);
            $this->addAction($app, RequestCoins::class);
            $this->addAction($app, CancelRequest::class);
            $this->addAction($app, ApproveRequest::class);
        }, WebDelivery::init()));
    }

    private function addAction(WebApplication $app, $class) {
        $app->actions->add((new \ReflectionClass($class))->getShortName(), new GenericObjectAction($class, $app->types, $app->parser, function ($action) {
            return $this->application->handle($action);
        }));
    }

    private function configureStores() {
        TransformerRegistryRepository::getDefaultTransformerRegistry()
            ->insert(new BinaryTransformer(TransformerRegistryRepository::getDefaultTypeMapper()))
            ->insert(new IdentifierTransformer(TransformerRegistryRepository::getDefaultTypeMapper()));
    }
}