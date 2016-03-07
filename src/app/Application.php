<?php
namespace groupcash\bank\app;

use groupcash\bank\app\sourced\Builder;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\app\sourced\domain\Projection;
use groupcash\bank\app\sourced\DomainEventListener;
use groupcash\bank\app\sourced\MessageHandler;
use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\app\sourced\messaging\Query;
use groupcash\bank\app\sourced\store\EventStore;
use groupcash\bank\DeliverCoin;
use groupcash\bank\events\CoinIssued;
use groupcash\bank\model\Account;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\Bank;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\model\Currency;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\php\Groupcash;

class Application implements Builder, DomainEventListener {

    /** @var MessageHandler */
    private $handler;

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

        $this->handler = new MessageHandler($events, $this);
        $this->handler->addListener($this);
    }

    public function handle($message) {
        return $this->handler->handle($message);
    }

    /**
     * @param Command $command
     * @return AggregateIdentifier
     * @throws \Exception
     */
    public function getAggregateIdentifier(Command $command) {
        if ($command instanceof ApplicationCommand) {
            return $command->getAggregateIdentifier($this->auth);
        }

        throw new \Exception("Not an application command.");
    }

    /**
     * @param AggregateIdentifier $identifier
     * @return AggregateRoot
     * @throws \Exception
     */
    public function buildAggregateRoot(AggregateIdentifier $identifier) {
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
     * @param Query $query
     * @return Projection
     * @throws \Exception
     */
    public function buildProjection(Query $query) {
        throw new \Exception('Unknown query.');
    }

    protected function onCoinIssued(CoinIssued $e) {
        $this->handle(new DeliverCoin(
            $e->getBacker(),
            $e->getCoin()
        ));
    }

    /**
     * @param DomainEvent $event
     * @return bool
     */
    public function listensTo(DomainEvent $event) {
        $method = 'on' . (new \ReflectionClass($event))->getShortName();
        return method_exists($this, $method);
    }

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function on(DomainEvent $event) {
        $method = 'on' . (new \ReflectionClass($event))->getShortName();
        call_user_func([$this, $method], $event);
    }
}