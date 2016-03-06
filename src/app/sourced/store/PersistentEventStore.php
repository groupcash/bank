<?php
namespace groupcash\bank\app\sourced\store;

use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\app\sourced\domain\EventStream;
use watoki\stores\file\FileStore;
use watoki\stores\Store;

class PersistentEventStore implements EventStore {

    /** @var Store */
    private $store;

    public function __construct($dataDir) {
        $this->store = FileStore::forClass(EventStream::class, $dataDir);
    }

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @return EventStream
     */
    public function read(AggregateIdentifier $aggregateIdentifier) {
        $id = (string)$aggregateIdentifier;
        if (!$this->has($aggregateIdentifier)) {
            $this->store->create(new EventStream(), $id);
        }
        return $this->store->read($id);
    }

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @param EventStream $stream
     */
    public function save(AggregateIdentifier $aggregateIdentifier, EventStream $stream) {
        $this->store->update($stream);
    }

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @return boolean
     */
    public function has(AggregateIdentifier $aggregateIdentifier) {
        return $this->store->hasKey((string)$aggregateIdentifier);
    }

    /**
     * @return EventStream
     */
    public function readAll() {
        $all = new EventStream();

        foreach ($this->store->keys() as $key) {
            /** @var EventStream $stream */
            $stream = $this->store->read($key);
            foreach ($stream->getEvents() as $event) {
                $all->add($event);
            }
        }

        return $all;
    }

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @param DomainEvent $event
     * @return void
     */
    public function add(AggregateIdentifier $aggregateIdentifier, DomainEvent $event) {
        $stream = $this->read($aggregateIdentifier);
        $stream->add($event);
        $this->save($aggregateIdentifier, $stream);
    }
}