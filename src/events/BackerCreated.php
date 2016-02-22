<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\BackerIdentifier;

class BackerCreated extends DomainEvent {

    /** @var BackerIdentifier */
    private $backer;

    /** @var string */
    private $backerKey;

    /** @var string */
    private $name;

    /**
     * @param BackerIdentifier $backer
     * @param string $backerKey
     * @param string $name
     */
    public function __construct(BackerIdentifier $backer, $backerKey, $name) {
        parent::__construct();

        $this->backer = $backer;
        $this->backerKey = $backerKey;
        $this->name = $name;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }

    /**
     * @return string
     */
    public function getBackerKey() {
        return $this->backerKey;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}