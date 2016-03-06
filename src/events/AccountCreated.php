<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\php\model\signing\Binary;

class AccountCreated extends DomainEvent {

    /** @var Binary */
    private $address;

    /**
     * @param Binary $address
     */
    public function __construct(Binary $address) {
        parent::__construct();
        $this->address = $address;
    }
}