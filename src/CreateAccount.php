<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BankIdentifier;

class CreateAccount implements ApplicationCommand {

    /** @var string|null */
    private $password;

    /**
     * @param null|string $password
     */
    public function __construct($password) {
        $this->password = $password;
    }

    /**
     * @return null|string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param Authenticator $authenticator
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $authenticator) {
        return BankIdentifier::singleton();
    }
}