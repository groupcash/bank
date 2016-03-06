<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\store\EventStore;

class ApplicationContext {

    /** @var EventStore */
    private $events;

    /**
     * @param EventStore $events
     */
    public function __construct(EventStore $events) {
        $this->events = $events;
    }
}