<?php
namespace groupcash\bank\events;

use groupcash\bank\app\sourced\domain\DomainEvent;
use groupcash\php\model\Authorization;

class IssuerAuthorized extends DomainEvent {

    /** @var Authorization */
    private $authorization;

    /**
     * @param Authorization $authorization
     */
    public function __construct(Authorization $authorization) {
        parent::__construct();

        $this->authorization = $authorization;
    }

    /**
     * @return Authorization
     */
    public function getAuthorization() {
        return $this->authorization;
    }
}