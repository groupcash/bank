<?php
namespace groupcash\bank\app\sourced\messaging;

abstract class Identifier {

    /** @var string */
    private $identifier;

    /**
     * @param string $identifier
     */
    public function __construct($identifier) {
        $this->identifier = $identifier;
    }

    function __toString() {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }
}