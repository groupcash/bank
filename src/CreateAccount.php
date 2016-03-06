<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\BankIdentifier;

class CreateAccount implements ApplicationCommand {

    /**
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier() {
        return BankIdentifier::singleton();
    }
}