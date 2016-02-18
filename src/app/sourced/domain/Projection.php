<?php
namespace groupcash\bank\app\sourced\domain;

class Projection {

    public function apply(EventStream $stream) {
        foreach ($stream->getEvents() as $event) {
            $method = 'apply' . (new \ReflectionClass($event))->getShortName();
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $event);
            }
        }
    }
}