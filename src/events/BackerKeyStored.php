<?php
namespace groupcash\bank\events;

use groupcash\php\model\signing\Binary;

class BackerKeyStored extends DomainEvent {

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