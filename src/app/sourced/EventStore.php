<?php
namespace groupcash\bank\app\sourced;

interface EventStore {

    /**
     * @return mixed[] All events appended to any aggregate
     */
    public function allEvents();

    /**
     * @param mixed $aggregateIdentifier
     * @return mixed[] Events appended to aggregate
     */
    public function eventsOf($aggregateIdentifier);

    /**
     * @param mixed $event
     * @param mixed $aggregateIdentifier
     * @return void
     */
    public function append($event, $aggregateIdentifier);
}