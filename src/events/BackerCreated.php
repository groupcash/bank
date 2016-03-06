<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\php\model\signing\Binary;

class BackerCreated extends DomainEvent {

    /** @var Binary */
    private $key;

    /**
     * @param Binary $key
     */
    public function __construct(Binary $key) {
        parent::__construct();

        $this->key = $key;
    }

    /**
     * @return Binary
     */
    public function getKey() {
        return $this->key;
    }
}