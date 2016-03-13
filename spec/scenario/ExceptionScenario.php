<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\sourced\Specification;

class ExceptionScenario {

    /** @var ApplicationCapabilities */
    private $app;

    /** @var Specification */
    private $specification;

    public function __construct(ApplicationCapabilities $app, Specification $specification) {
        $this->app = $app;
        $this->specification = $specification;
    }

    function __call($name, $arguments) {
        $this->specification->tryTo(function () use ($name, $arguments) {
            call_user_func_array([$this->app, $name], $arguments);
        });
    }
}