<?php
namespace groupcash\bank\app\sourced\domain;

use groupcash\bank\app\sourced\messaging\Command;

abstract class AggregateRoot {

    /** @var DomainEvent[] */
    private $recordedEvents = [];

    public function handle(Command $command) {
        $handleMethod = 'handle' . (new \ReflectionClass($command))->getShortName();
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