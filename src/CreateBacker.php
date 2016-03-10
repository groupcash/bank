<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\model\Identifier;

class CreateBacker implements Command {

    /** @var null|string */
    private $name;

    /** @var null|string */
    private $details;

    /**
     * @param string|null $name
     * @param string|null $details
     */
    public function __construct($name = null, $details = null) {
        $this->name = $name;
        $this->details = $details;
    }

    /**
     * @return null|string
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