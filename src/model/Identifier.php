<?php
namespace groupcash\bank\model;

use groupcash\php\model\signing\Binary;

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

    public static function fromBinary(Binary $binary) {
        return new static(base64_encode($binary->getData()));
    }

    public function toBinary() {
        return new Binary(base64_decode($this->__toString()));
    }
}