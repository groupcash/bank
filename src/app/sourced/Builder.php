<?php
namespace groupcash\bank\app\sourced;

use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\app\sourced\domain\Projection;
use groupcash\bank\app\sourced\messaging\Command;
use groupcash\bank\app\sourced\messaging\Query;

interface Builder {

    /**
     * @param Command $command
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Command $command);

    /**
     * @param AggregateIdentifier $identifier
     * @return AggregateRoot
     */
    public function buildAggregateRoot(AggregateIdentifier $identifier);

    /**
     * @param Query $query
     * @return Projection
     */
    public function buildProjection(Query $query);
}