<?php
namespace groupcash\bank\app\sourced\store;

use groupcash\bank\app\sourced\domain\EventStream;
use groupcash\bank\app\sourced\messaging\Identifier;

interface EventStore {

    /**
     * @return EventStream
     */
    public function readAll();

    /**
     * @param Identifier $aggregateIdentifier
     * @return EventStream
     */
    public function read(Identifier $aggregateIdentifier);

    /**
     * @param Identifier $aggregateIdentifier
     * @param EventStream $stream
     * @return
     */
    public function save(Identifier $aggregateIdentifier, EventStream $stream);

    /**
     * @param Identifier $aggregateIdentifier
     * @return boolean
     */
    public function has(Identifier $aggregateIdentifier);
}