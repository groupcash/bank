<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\Time;
use rtens\scrut\Fixture;
use rtens\scrut\fixtures\ExceptionFixture;

class Scenario extends Fixture {

    /** @var ApplicationFixture */
    public $tryThat;

    /** @var ApplicationFixture */
    public $given;

    /** @var ApplicationFixture */
    public $when;

    /** @var ApplicationFixture */
    public $then;

    public function before() {
        Time::$frozen = new \DateTimeImmutable();

        $except = new ExceptionFixture($this->assert);
        $app = new ApplicationFixture($this->assert, $except);

        $this->tryThat = new ExceptionScenario($app, $except);
        $this->given = $app;
        $this->when = $app;
        $this->then = $app;
    }

    public function blockedBy($capability) {
        $this->assert->incomplete("Blocked by $capability");
    }
}