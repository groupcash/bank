<?php
namespace groupcash\bank\app\sourced\domain;

class EventStream {

    /** @var DomainEvent[] */
    private $events = [];

    /**
     * @return DomainEvent[]
     */
    public function getEvents() {
        return $this->events;
    }

    public function add(DomainEvent $event) {
        $this->events[] = $event;
    }
}