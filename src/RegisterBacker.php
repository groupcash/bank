<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\model\Identifier;

class RegisterBacker implements Command {

    /** @var AccountIdentifier */
    private $account;

    /** @var string */
    private $name;

    /** @var null|string */
    private $details;

    /**
     * @param AccountIdentifier $account
     * @param string $name
     * @param null|string $details
     */
    public function __construct(AccountIdentifier $account, $name, $details = null) {
        $this->account = $account;
        $this->name = $name;
        $this->details = $details;
    }

    /**
     * @return AccountIdentifier
     */
    public function getAccount() {
        return $this->account;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getDetails() {
        return $this->details;
    }

    /**
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return BankIdentifier::singleton();
    }
}