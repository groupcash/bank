<?php
namespace groupcash\bank\app;

use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\app\sourced\messaging\Command;

interface ApplicationCommand extends Command {

    /**
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier();
}