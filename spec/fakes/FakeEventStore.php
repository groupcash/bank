<?php
namespace spec\groupcash\bank\fakes;

use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\app\sourced\domain\EventStream;
use groupcash\bank\app\sourced\store\EventStore;

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
     * @param AggregateIdentifier $identifier
     * @param callable $filter
     * @return DomainEvent[]
     */
    public function filter(AggregateIdentifier $identifier, callable $filter) {
        return array_filter($this->read($identifier)->getEvents(), $filter);
    }

    /**
     * @param AggregateIdentifier $identifier
     * @param string $class
     * @return DomainEvent[]
     */
    public function filterClass(AggregateIdentifier $identifier, $class) {
        return $this->filter($identifier, function (DomainEvent $event) use ($class) {
            return is_a($event, $class);
        });
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