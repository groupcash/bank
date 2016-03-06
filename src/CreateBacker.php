<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\BankIdentifier;

class CreateBacker implements ApplicationCommand {
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
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier() {
        return BankIdentifier::singleton();
    }
}