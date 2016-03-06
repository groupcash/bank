<?php
namespace groupcash\bank\app\sourced\store;

use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\app\sourced\domain\EventStream;

class FakeEventStore implements EventStore {

    /** @var EventStream[] */
    private $streams = [];

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @return EventStream
     */
    public function read(AggregateIdentifier $aggregateIdentifier) {
        if (!$this->has($aggregateIdentifier)) {
            $this->save($aggregateIdentifier, new EventStream());
        }
        return $this->streams[(string)$aggregateIdentifier];
    }

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @param EventStream $stream
     */
    public function save(AggregateIdentifier $aggregateIdentifier, EventStream $stream) {
        $this->streams[(string)$aggregateIdentifier] = $stream;
    }

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @return boolean
     */
    public function has(AggregateIdentifier $aggregateIdentifier) {
        return isset($this->streams[(string)$aggregateIdentifier]);
    }

    /**
     * @return EventStream
     */
    public function readAll() {
        $all = new EventStream();
        foreach ($this->streams as $stream) {
            foreach ($stream->getEvents() as $event) {
                $all->add($event);
            }
        }
        return $all;
    }
}