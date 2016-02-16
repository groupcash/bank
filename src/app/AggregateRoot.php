<?php
namespace groupcash\bank\app;

abstract class AggregateRoot {

    /** @var Event[] */
    private $recordedEvents = [];

    /**
     * @param EventStream $stream
     */
    public function reconstitute(EventStream $stream) {
        $this->applyStream($stream);
    }

    private function applyStream(EventStream $stream) {
        foreach ($stream->getEvents() as $event) {
            $this->apply($event);
        }
    }

    private function apply($event) {
        $method = 'apply' . (new \ReflectionClass($event))->getShortName();
        if (method_exists($this, $method)) {
            call_user_func([$this, $method], $event);
        }
    }

    /**
     * @return Event[]
     */
    public function getRecordedEvents() {
        return $this->recordedEvents;
    }

    protected function record(Event $event) {
        $this->recordedEvents[] = $event;
    }
}