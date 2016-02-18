<?php
namespace groupcash\bank\app\sourced\messaging;

interface Command extends DomainMessage {

    /**
     * @return Identifier
     */
    public function getAggregateIdentifier();
}