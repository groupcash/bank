<?php
namespace groupcash\bank\events;

use groupcash\bank\model\BackerIdentifier;

class BackerRegistered extends DomainEvent {

    /** @var BackerIdentifier */
    private $backer;

    /** @var string */
    private $name;

    /**
     * @param BackerIdentifier $backer
     * @param string $name
     */
    public function __construct(BackerIdentifier $backer, $name) {
        parent::__construct();

        $this->backer = $backer;
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
    public function getName() {
        return $this->name;
    }
}