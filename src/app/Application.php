<?php
namespace groupcash\bank\app;

use groupcash\bank\app\sourced\command\AggregateFactory;
use groupcash\bank\app\sourced\command\CommandHandler;
use groupcash\bank\app\sourced\command\EventListener;
use groupcash\bank\app\sourced\EventStore;
use groupcash\bank\app\sourced\query\ProjectionFactory;
use groupcash\bank\app\sourced\query\QueryProjector;
use groupcash\bank\CreateAccount;
use groupcash\bank\DeliverCoin;
use groupcash\bank\events\CoinIssued;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\model\Account;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\Bank;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\model\CreatedAccount;
use groupcash\bank\model\Currency;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\RegisterAccount;
use groupcash\php\Groupcash;
use groupcash\php\model\signing\Binary;

class Application implements AggregateFactory, ProjectionFactory, EventListener {

    /** @var CommandHandler */
    private $command;

    /** @var QueryProjector */
    private $query;

    /** @var Groupcash */
    private $library;

    /** @var Cryptography */
    private $crypto;

    /** @var Authenticator */
    private $auth;

    /**
     * @param EventStore $events
     * @param Groupcash $library
     * @param Cryptography $crypto
     */
    public function __construct(EventStore $events, Groupcash $library, Cryptography $crypto) {
        $this->library = $library;
        $this->crypto = $crypto;
        $this->auth = new Authenticator($crypto, $library);

        $this->query = new QueryProjector($events, $this);
        $this->command = new CommandHandler($events, $this);
        $this->command->addListener($this);
    }

    public function handle($message) {
        if ($message instanceof CreateAccount) {
            return $this->createAccount($message);
        } else if ($message instanceof Command) {
            $this->command->handle($message);
            return null;
        } else {
            return $this->query->project($message);
        }
    }

    private function createAccount(CreateAccount $c) {
        $key = $this->library->generateKey();
        $address = $this->library->getAddress($key);

        if ($c->getPassword()) {
            $key = new Binary($this->crypto->encrypt($key->getData(), $c->getPassword()));
        }

        $this->command->handle(new RegisterAccount($address));

        return new CreatedAccount($key, $address);
    }

    protected function onCoinIssued(CoinIssued $e) {
        $this->handle(new DeliverCoin(
            $e->getBacker(),
            $e->getCurrency(),
            $e->getCoin()
        ));
    }

    protected function onCoinsSent(CoinsSent $e) {
        $this->handle(new DeliverCoin(
            $e->getTarget(),
            $e->getCurrency(),
            $e->getTransferred()
        ));
    }

    /**
     * @param mixed $identifier
     * @return object
     * @throws \Exception
     */
    public function buildAggregateRoot($identifier) {
        if ($identifier instanceof BankIdentifier) {
            return new Bank($this->library, $this->crypto);
        } else if ($identifier instanceof CurrencyIdentifier) {
            return new Currency($this->library, $this->crypto);
        } else if ($identifier instanceof AccountIdentifier) {
            return new Account($this->library, $this->crypto);
        }

        throw new \Exception('Unknown aggregate.');
    }

    /**
     * @param object $query
     * @return object
     * @throws \Exception
     */
    public function buildProjection($query) {
        throw new \Exception('Unknown query.');
    }

    /**
     * @param Command $command
     * @return mixed
     * @throws \Exception
     */
    public function getAggregateIdentifier($command) {
        return $command->getAggregateIdentifier($this->auth);
    }

    /**
     * @param object $event
     * @return bool
     */
    public function listensTo($event) {
        $method = 'on' . (new \ReflectionClass($event))->getShortName();
        return method_exists($this, $method);
    }

    /**
     * @param object $event
     * @return void
     */
    public function on($event) {
        $method = 'on' . (new \ReflectionClass($event))->getShortName();
        call_user_func([$this, $method], $event);
    }

    /**
     * @param object $command
     * @return string
     */
    public function handleMethod($command) {
        return 'handle' . (new \ReflectionClass($command))->getShortName();
    }

    /**
     * @param object $event
     * @return string
     */
    public function applyMethod($event) {
        return 'apply' . (new \ReflectionClass($event))->getShortName();
    }
}