<?php
namespace groupcash\bank\app\sourced;

use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\app\sourced\domain\Projection;
use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\app\sourced\messaging\Query;

interface Builder {

    /**
     * @param Command $command
     * @return AggregateRoot
     */
    public function buildAggregateRoot(Command $command);

    /**
     * @param Query $query
     * @return Projection
     */
    public function buildProjection(Query $query);
}