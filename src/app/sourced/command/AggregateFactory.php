<?php
namespace groupcash\bank\app\sourced\command;

interface AggregateFactory {

    /**
     * @param mixed $command
     * @return string
     */
    public function handleMethod($command);

    /**
     * @param mixed $event
     * @return string
     */
    public function applyMethod($event);

    /**
     * @param object $command
     * @return mixed
     */
    public function getAggregateIdentifier($command);

    /**
     * @param mixed $identifier
     * @return object
     */
    public function buildAggregateRoot($identifier);
}