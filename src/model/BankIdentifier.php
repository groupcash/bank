<?php
namespace groupcash\bank\model;

use groupcash\bank\app\sourced\messaging\Identifier;

class BankIdentifier extends Identifier {

    public static function singleton() {
        return new BankIdentifier('__bank');
    }
}