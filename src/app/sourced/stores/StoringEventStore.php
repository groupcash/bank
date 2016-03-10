<?php
namespace groupcash\bank\app\sourced\stores;

use groupcash\bank\app\sourced\EventStore;
use watoki\stores\Store;

class StoringEventStore implements EventStore {

    /** @var Store */
    private $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store) {
        $this->store = $store;
    }

    /**
     * @param mixed $aggregateIdentifier
     * @return mixed[]
     */
    public function eventsOf($aggregateIdentifier) {
        if (!$this->store->has($aggregateIdentifier)) {
            return [];
        }
        return $this->store->read($aggregateIdentifier);
    }

    /**
     * @return mixed[]
     */
    public function allEvents() {
        $all = [];
        foreach ($this->store->keys() as $key) {
            $all = array_merge($all, $this->store->read($key));
        }
        return $all;
    }

    /**
     * @param mixed $event
     * @param mixed $aggregateIdentifier
     */
    public function append($event, $aggregateIdentifier) {
        $events = $this->eventsOf($aggregateIdentifier);
        $events[] = $event;
        $this->store->write($events, $aggregateIdentifier);
    }
}