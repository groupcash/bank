<?php
namespace groupcash\bank\app;

class Projection {

    public function __construct(EventStream $stream) {
        foreach ($stream->getEvents() as $event) {
            $method = 'apply' . (new \ReflectionClass($event))->getShortName();
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $event);
            }
        }
    }
}