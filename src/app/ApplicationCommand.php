<?php
namespace groupcash\bank\app;

use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\model\Authenticator;

interface ApplicationCommand extends Command {

    /**
     * @param Authenticator $authenticator
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $authenticator);
}