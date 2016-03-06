<?php
namespace groupcash\bank\app\sourced\domain;

use groupcash\bank\app\sourced\messaging\Command;

abstract class AggregateRoot {

    /** @var DomainEvent[] */
    private $recordedEvents = [];

    public function handle(Command $command) {
        $commandName = (new \ReflectionClass($command))->getShortName();
        $handleMethod = 'handle' . $commandName;

        if (!method_exists($this, $handleMethod)) {
            throw new \Exception("Missing method in " . (new \ReflectionClass($this))->getFileName() . "\n" .
                "\t\tprotected function $handleMethod($commandName \$c) {}");
        }

        return call_user_func([$this, $handleMethod], $command);
    }

    public function reconstitute(EventStream $stream) {
        $this->applyStream($stream);
    }

    private function applyStream(EventStream $stream) {
        foreach ($stream->getEvents() as $event) {
            $this->apply($event);
        }
    }

    private function apply(DomainEvent $event) {
        $method = 'apply' . (new \ReflectionClass($event))->getShortName();
        if (method_exists($this, $method)) {
            call_user_func([$this, $method], $event);
        }
    }

    /**
     * @return DomainEvent[]
     */
    public function getRecordedEvents() {
        return $this->recordedEvents;
    }

    protected function record(DomainEvent $event) {
        $this->recordedEvents[] = $event;
    }
}