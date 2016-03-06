<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\php\model\signing\Binary;

class BackerRegistered extends DomainEvent {

    /** @var Binary */
    private $address;

    /** @var string */
    private $name;

    /**
     * @param Binary $address
     * @param string $name
     */
    public function __construct(Binary $address, $name) {
        parent::__construct();

        $this->address = $address;
        $this->name = $name;
    }

    /**
     * @return Binary
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}