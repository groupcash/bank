<?php
namespace groupcash\bank\model;

class BankIdentifier extends Identifier{

    public static function singleton() {
        return new BankIdentifier('__bank');
    }
}