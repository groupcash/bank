<?php
namespace groupcash\bank\app\sourced;

use groupcash\bank\app\sourced\command\EventListener;
use groupcash\bank\app\sourced\stores\MemoryEventStore;
use watoki\reflect\ValuePrinter;

abstract class Specification {

    /** @var EventStore */
    protected $events;

    /** @var mixed */
    protected $returned;

    /** @var null|\Exception */
    private $caught;

    public function __construct() {
        $this->events = $this->makeEventStore();
    }

    /**
     * @return EventListener[]
     */
    protected abstract function listeners();

    /**
     * @param mixed $commandOrQuery
     * @return mixed
     */
    protected abstract function handle($commandOrQuery);

    protected function makeEventStore() {
        return new MemoryEventStore();
    }

    /**
     * @param string $message
     * @throws \Exception
     */
    protected function fail($message) {
        throw new \Exception($message);
    }

    public function given($event, $aggregateIdentifier) {
        $this->events->append($event, $aggregateIdentifier);
    }

    public function when($message) {
        foreach ($this->events->allEvents() as $event) {
            foreach ($this->listeners() as $listener) {
                if ($listener->listensTo($event)) {
                    $listener->on($event);
                }
            }
        }
        $this->returned = $this->handle($message);
    }

    public function tryTo($messageOrCallable) {
        $this->caught = null;
        try {
            if (is_callable($messageOrCallable)) {
                $messageOrCallable();
            } else {
                $this->when($messageOrCallable);
            }
        } catch (\Exception $e) {
            $this->caught = $e;
        }
    }

    public function thenShould($eventOrClass, callable $condition = null) {
        if (!$this->filterEvents($eventOrClass, $condition)) {
            $matching = $condition ? 'matching the condition' : '';
            $this->fail("No event [" . ValuePrinter::serialize($eventOrClass) . "] $matching was recorded.");
        }
    }

    public function thenShouldNot($eventOrClass, callable $condition = null, $conditionDescription = null) {
        $count = count($this->filterEvents($eventOrClass, $condition));
        if ($count) {
            $this->fail(
                ($count == 1 ? "One event" : "$count events") .
                " [" . ValuePrinter::serialize($eventOrClass) . "] matching [$conditionDescription] " .
                ($count == 1 ? "was" : "were") .
                " unexpectedly recorded.");
        }
    }

    public function thenItShouldReturn(callable $condition) {
        if (!$condition($this->returned)) {
            $this->fail('The returned value does not match the conditions.');
        }
    }

    public function thenItShouldFailWith($message, $exceptionClass = null) {
        if (!$this->caught) {
            $this->fail('No exception was thrown.');
        }

        if ($this->caught->getMessage() != $message) {
            $this->fail("Exception was [{$this->caught->getMessage()}] instead of [$message]");
        }

        if ($exceptionClass && !is_a($this->caught, $exceptionClass)) {
            $this->fail("Exception was of type [" . get_class($this->caught) . "] instead of [$exceptionClass]");
        }
    }

    private function filterEvents($eventOrClass, callable $condition = null) {
        return array_filter($this->events->allEvents(), function ($event) use ($eventOrClass, $condition) {
            if (is_object($eventOrClass)) {
                return $event == $eventOrClass;
            } else if ($eventOrClass) {
                return is_a($event, $eventOrClass);
            } else if ($condition) {
                return $condition($event);
            } else {
                throw new \Exception('Event insufficiently specified.');
            }
        });
    }
}