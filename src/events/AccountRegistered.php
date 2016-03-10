<?php
namespace groupcash\bank\events;

use groupcash\bank\model\AccountIdentifier;

class AccountRegistered extends DomainEvent {

    /** @var AccountIdentifier */
    private $account;

    /**
     * @param AccountIdentifier $account
     */
    public function __construct(AccountIdentifier $account) {
        parent::__construct();
        $this->account = $account;
    }

    /**
     * @return AccountIdentifier
     */
    public function getAccount() {
        return $this->account;
    }
}