<?php
namespace groupcash\bank\app;

use groupcash\bank\model\Authenticator;
use groupcash\bank\model\Identifier;

interface Command {

    /**
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth);
}