<?php
namespace groupcash\bank\app;

use groupcash\bank\model\Identifier;

interface EventStore {

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