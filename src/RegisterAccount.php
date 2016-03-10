<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\model\Identifier;
use groupcash\php\model\signing\Binary;

class RegisterAccount implements Command {

    /** @var Binary */
    private $address;

    /**
     * @param Binary $address
     */
    public function __construct(Binary $address) {
        $this->address = $address;
    }

    /**
     * @return Binary
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return BankIdentifier::singleton();
    }
}