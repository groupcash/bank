<?php
namespace groupcash\bank\app;

class EventStream {

    /** @var Event[] */
    private $events = [];

    /**
     * @return Event[]
     */
    public function getEvents() {
        return $this->events;
    }

    public function add(Event $event) {
        $this->events[] = $event;
    }
}