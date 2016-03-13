<?php
namespace spec\groupcash\bank\scenario;

use groupcash\bank\app\Application;
use groupcash\bank\app\crypto\FakeCryptography;
use groupcash\bank\app\sourced\command\EventListener;
use groupcash\bank\app\sourced\Specification;
use groupcash\php\algorithms\FakeAlgorithm;
use groupcash\php\Groupcash;

class ApplicationSpecification extends Specification {

    /** @var Application */
    private $app;

    public function __construct() {
        parent::__construct();
        $this->app = new Application($this->events, new Groupcash(new FakeAlgorithm()), new FakeCryptography());
    }

    /**
     * @return EventListener[]
     */
    protected function listeners() {
        return [$this->app];
    }

    /**
     * @param mixed $commandOrQuery
     * @return mixed
     */
    protected function handle($commandOrQuery) {
        return $this->app->handle($commandOrQuery);
    }
}