<?php
namespace groupcash\bank\app\sourced;

use groupcash\bank\app\sourced\domain\DomainEvent;

interface DomainEventListener {

    /**
     * @param DomainEvent $event
     * @return bool
     */
    public function listensTo(DomainEvent $event);

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function on(DomainEvent $event);
}