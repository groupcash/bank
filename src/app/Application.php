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

class Application implements Builder, DomainEventListener {

    /** @var MessageHandler */
    private $handler;

    /**
     * @param EventStore $events
     */
    public function __construct(EventStore $events) {
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
        throw new \Exception("Not an application command.");
    }

    /**
     * @param AggregateIdentifier $identifier
     * @return AggregateRoot
     * @throws \Exception
     */
    public function buildAggregateRoot(AggregateIdentifier $identifier) {
        throw new \Exception('Unknown command.');
    }

    /**
     * @param Query $query
     * @return Projection
     * @throws \Exception
     */
    public function buildProjection(Query $query) {
        throw new \Exception('Unknown query.');
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