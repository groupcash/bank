<?php
namespace groupcash\bank;

use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\model\AccountIdentifier;

class RegisterCurrency implements Command {

    /** @var AccountIdentifier */
    private $currency;

    /** @var string */
    private $name;

    /**
     * @param AccountIdentifier $currency
     * @param string $name
     * @throws \Exception
     */
    public function __construct(AccountIdentifier $currency, $name) {
        $this->currency = $currency;
        $this->name = trim($name);

        if (!$this->name) {
            throw new \Exception('The currency name cannot be empty.');
        }
    }

    /**
     * @return AccountIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}