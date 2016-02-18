<?php
namespace groupcash\bank\app\sourced;

use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\app\sourced\messaging\DomainMessage;
use groupcash\bank\app\sourced\messaging\Query;
use groupcash\bank\app\sourced\store\EventStore;

class MessageHandler {

    /** @var EventStore */
    private $store;

    /** @var Builder */
    private $builder;

    /** @var DomainEventListener[] */
    private $listeners = [];

    /**
     * @param EventStore $store
     * @param Builder $builder
     */
    public function __construct(EventStore $store, Builder $builder) {
        $this->store = $store;
        $this->builder = $builder;
    }

    public function addListener(DomainEventListener $listener) {
        $this->listeners[] = $listener;
    }

    public function handle(DomainMessage $message) {
        if ($message instanceof Command) {
            return $this->handleCommand($message);
        } else if ($message instanceof Query) {
            return $this->handleQuery($message);
        }

        throw new \Exception('Cannot handle [' . get_class($message) . '].');
    }

    private function handleCommand(Command $command) {
        $identifier = $command->getAggregateIdentifier();

        $stream = $this->store->read($identifier);

        $aggregate = $this->builder->buildAggregateRoot($command);
        $aggregate->reconstitute($stream);

        $returned = $aggregate->handle($command);

        foreach ($aggregate->getRecordedEvents() as $event) {
            $stream->add($event);
        }
        $this->store->save($identifier, $stream);

        foreach ($this->listeners as $listener) {
            foreach ($aggregate->getRecordedEvents() as $event) {
                if ($listener->listensTo($event)) {
                    $listener->on($event);
                }
            }
        }
        return $returned;
    }

    private function handleQuery(Query $query) {
        $projection = $this->builder->buildProjection($query);
        $projection->apply($this->store->readAll());

        return $projection;
    }
}