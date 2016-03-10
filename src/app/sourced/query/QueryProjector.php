<?php
namespace groupcash\bank\app\sourced\query;

use groupcash\bank\app\sourced\EventStore;

class QueryProjector {

    /** @var EventStore */
    private $store;

    /** @var ProjectionFactory */
    private $projections;

    /**
     * @param EventStore $store
     * @param ProjectionFactory $projections
     */
    public function __construct(EventStore $store, ProjectionFactory $projections) {
        $this->store = $store;
        $this->projections = $projections;
    }

    /**
     * @param mixed $query
     * @return object
     */
    public function project($query) {
        $projection = $this->projections->buildProjection($query);
        $this->applyEvents($projection, $this->store->allEvents());

        return $projection;
    }

    private function applyEvents($object, array $events) {
        foreach ($events as $event) {
            $method = $this->projections->applyMethod($event);
            if (method_exists($object, $method)) {
                $object->$method($event);
            }
        }
    }
}