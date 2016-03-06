<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\BackerIdentifier;
use groupcash\php\model\signing\Binary;

class BackerCreated extends DomainEvent {

    /** @var string */
    private $key;

    /** @var BackerIdentifier */
    private $backer;

    /**
     * @param BackerIdentifier $backer
     * @param Binary $key
     */
    public function __construct(BackerIdentifier $backer, Binary $key) {
        parent::__construct();

        $this->key = base64_encode($key->getData());
        $this->backer = $backer;
    }

    /**
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }
}