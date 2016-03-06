<?php
namespace spec\groupcash\bank\scenario;

use rtens\scrut\Assert;
use rtens\scrut\fixtures\ExceptionFixture;

class ApplicationFixture {

    /** @var ExceptionFixture */
    private $except;

    /** @var Assert */
    private $assert;

    public function __construct(Assert $assert, ExceptionFixture $except) {
        $this->assert = $assert;
        $this->except = $except;
    }
}