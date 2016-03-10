<?php
namespace groupcash\bank\app\sourced\command;

use groupcash\bank\app\sourced\EventStore;

class CommandHandler {

    /** @var EventStore */
    private $store;

    /** @var AggregateFactory */
    private $aggregates;

    /** @var EventListener[] */
    private $listeners = [];

    /**
     * @param EventStore $store
     * @param AggregateFactory $aggregates
     */
    public function __construct(EventStore $store, AggregateFactory $aggregates) {
        $this->store = $store;
        $this->aggregates = $aggregates;
    }

    /**
     * @param EventListener $listener
     */
    public function addListener(EventListener $listener) {
        $this->listeners[] = $listener;
    }

    /**
     * @param mixed $command
     * @return void
     * @throws \Exception
     */
    public function handle($command) {
        $identifier = $this->aggregates->getAggregateIdentifier($command);
        $aggregate = $this->aggregates->buildAggregateRoot($identifier);

        $this->applyEvents($aggregate, $this->store->eventsOf($identifier));

        $events = $this->handleCommand($aggregate, $command);

        if (!$events) {
            return;
        } else if (!is_array($events)) {
            $events = [$events];
        }

        $this->appendEvents($events, $identifier);
        $this->tellListeners($events);
    }

    private function applyEvents($object, array $events) {
        foreach ($events as $event) {
            $method = $this->aggregates->applyMethod($event);
            if (method_exists($object, $method)) {
                $object->$method($event);
            }
        }
    }

    private function handleCommand($aggregate, $command) {
        $method = $this->aggregates->handleMethod($command);

        if (!method_exists($aggregate, $method)) {
            throw new \Exception("Missing method " . get_class($aggregate) . "::{$method}()");
        }

        return $aggregate->$method($command);
    }

    private function appendEvents($events, $identifier) {
        foreach ($events as $event) {
            $this->store->append($event, $identifier);
        }
    }

    private function tellListeners($events) {
        foreach ($this->listeners as $listener) {
            foreach ($events as $event) {
                if ($listener->listensTo($event)) {
                    $listener->on($event);
                }
            }
        }
    }
}