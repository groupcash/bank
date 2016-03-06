<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\php\model\signing\Binary;

class BackerDetailsChanged extends DomainEvent {

    /** @var Binary */
    private $address;

    /** @var string */
    private $details;

    /**
     * @param Binary $address
     * @param string $details
     */
    public function __construct(Binary $address, $details) {
        parent::__construct();

        $this->address = $address;
        $this->details = $details;
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
    public function getDetails() {
        return $this->details;
    }
}