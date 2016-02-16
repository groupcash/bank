<?php
namespace groupcash\bank\app;

abstract class Event {

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