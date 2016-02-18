<?php
namespace spec\groupcash\bank\fakes;

use groupcash\bank\model\RandomNumberGenerator;

class FakeRandomNumberGenerator implements RandomNumberGenerator {

    /** @var string */
    private $randomNumber;

    /**
     * @param string $randomNumber
     */
    public function __construct($randomNumber) {
        $this->randomNumber = $randomNumber;
    }

    /**
     * @return string
     */
    public function generate() {
        return $this->randomNumber;
    }
}