<?php
namespace groupcash\bank\app\sourced\store;

use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\app\sourced\domain\EventStream;

interface EventStore {

    /**
     * @return EventStream
     */
    public function readAll();

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @return EventStream
     */
    public function read(AggregateIdentifier $aggregateIdentifier);

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @param EventStream $stream
     * @return
     */
    public function save(AggregateIdentifier $aggregateIdentifier, EventStream $stream);

    /**
     * @param AggregateIdentifier $aggregateIdentifier
     * @return boolean
     */
    public function has(AggregateIdentifier $aggregateIdentifier);
}