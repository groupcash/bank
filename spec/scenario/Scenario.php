<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\model\Time;
use rtens\scrut\failures\IncompleteTestFailure;

class Scenario {

    /** @var SpecificationCapabilities */
    public $tryThat;

    /** @var SpecificationContext */
    public $given;

    /** @var SpecificationCapabilities */
    public $when;

    /** @var SpecificationOutcome */
    public $then;

    public function before() {
        Time::$frozen = new \DateTimeImmutable();

        $specification = new ApplicationSpecification();

        $this->given = new SpecificationContext($specification);
        $this->when = new SpecificationCapabilities($specification);
        $this->then = new SpecificationOutcome($specification);
        $this->tryThat = new ExceptionScenario($this->when, $specification);
    }

    public function blockedBy($reason) {
        throw new IncompleteTestFailure("Blocked by $reason");
    }
}