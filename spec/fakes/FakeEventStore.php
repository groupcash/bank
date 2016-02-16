<?php
namespace spec\groupcash\bank\fakes;

use groupcash\bank\app\Event;
use groupcash\bank\app\EventStore;
use groupcash\bank\app\EventStream;
use groupcash\bank\model\Identifier;

class FakeEventStore implements EventStore {

    /** @var EventStream[] */
    private $streams = [];

    /**
     * @param Identifier $aggregateIdentifier
     * @return EventStream
     */
    public function read(Identifier $aggregateIdentifier) {
        if (!$this->has($aggregateIdentifier)) {
            $this->save($aggregateIdentifier, new EventStream());
        }
        return $this->streams[(string)$aggregateIdentifier];
    }

    /**
     * @param Identifier $aggregateIdentifier
     * @param EventStream $stream
     */
    public function save(Identifier $aggregateIdentifier, EventStream $stream) {
        $this->streams[(string)$aggregateIdentifier] = $stream;
    }

    /**
     * @param Identifier $aggregateIdentifier
     * @return boolean
     */
    public function has(Identifier $aggregateIdentifier) {
        return isset($this->streams[(string)$aggregateIdentifier]);
    }

    public function filter(Identifier $identifier, callable $filter) {
        return array_filter($this->read($identifier)->getEvents(), $filter);
    }

    public function filterClass(Identifier $identifier, $class) {
        return $this->filter($identifier, function (Event $event) use ($class) {
            return is_a($event, $class);
        });
    }
}