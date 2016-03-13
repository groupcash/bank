<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\model\Time;
use rtens\scrut\failures\IncompleteTestFailure;

class Scenario {

    /** @var ApplicationCapabilities */
    public $tryThat;

    /** @var ApplicationContext */
    public $given;

    /** @var ApplicationCapabilities */
    public $when;

    /** @var ApplicationOutcome */
    public $then;

    public function before() {
        Time::$frozen = new \DateTimeImmutable();

        $specification = new ApplicationSpecification();

        $this->given = new ApplicationContext($specification);
        $this->when = new ApplicationCapabilities($specification);
        $this->then = new ApplicationOutcome($specification);
        $this->tryThat = new ExceptionScenario($this->when, $specification);
    }

    public function blockedBy($reason) {
        throw new IncompleteTestFailure("Blocked by $reason");
    }
}