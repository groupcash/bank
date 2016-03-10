<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\stores\MemoryEventStore;
use groupcash\bank\model\Time;
use rtens\scrut\Fixture;
use rtens\scrut\fixtures\ExceptionFixture;

class Scenario extends Fixture {

    /** @var ApplicationCapabilities */
    public $tryThat;

    /** @var ApplicationContext */
    public $given;

    /** @var ApplicationCapabilities */
    public $when;

    /** @var ApplicationOutcome */
    public $then;

    public function before() {
        $except = new ExceptionFixture($this->assert);
        Time::$frozen = new \DateTimeImmutable();
        $returned = new ReturnValue();

        $events = new MemoryEventStore();

        $this->given = new ApplicationContext($events);
        $this->when = new ApplicationCapabilities($returned, $events);
        $this->then = new ApplicationOutcome($this->assert, $except, $returned, $events);
        $this->tryThat = new ExceptionScenario($this->when, $except);
    }

    public function blockedBy($reason) {
        $this->assert->incomplete("Blocked by $reason");
    }
}