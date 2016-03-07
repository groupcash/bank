<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BankIdentifier;

class CreateAccount implements ApplicationCommand {

    /** @var null|string */
    private $password;

    /**
     * @param null|string $password
     */
    public function __construct($password = null) {
        $this->password = $password;
    }

    /**
     * @return null|string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param Authenticator $auth
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return BankIdentifier::singleton();
    }
}