<?php
namespace groupcash\bank\events;

use groupcash\bank\model\Time;

abstract class DomainEvent {

    /** @var \DateTimeImmutable */
    private $when;

    /**
     * @param null|\DateTimeImmutable $when
     */
    public function __construct(\DateTimeImmutable $when = null) {
        $this->when = $when ?: Time::now();
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getWhen() {
        return $this->when;
    }
}