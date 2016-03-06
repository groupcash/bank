<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\bank\model\BackerIdentifier;

class BackerDetailsChanged extends DomainEvent {

    /** @var BackerIdentifier */
    private $backer;

    /** @var string */
    private $details;

    /**
     * @param BackerIdentifier $backer
     * @param string $details
     */
    public function __construct(BackerIdentifier $backer, $details) {
        parent::__construct();

        $this->backer = $backer;
        $this->details = $details;
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
    public function getDetails() {
        return $this->details;
    }
}