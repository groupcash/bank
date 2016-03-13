<?php
namespace groupcash\bank;

use groupcash\bank\model\Authentication;

class ListTransactions {

    /** @var Authentication */
    private $account;

    /**
     * @param Authentication $account
     */
    public function __construct(Authentication $account) {
        $this->account = $account;
    }

    /**
     * @return Authentication
     */
    public function getAccount() {
        return $this->account;
    }
}