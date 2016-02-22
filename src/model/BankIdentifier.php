<?php
namespace groupcash\bank\model;

use groupcash\bank\app\sourced\domain\AggregateIdentifier;

class BankIdentifier extends Identifier implements AggregateIdentifier{

    public static function singleton() {
        return new BankIdentifier('__bank');
    }
}