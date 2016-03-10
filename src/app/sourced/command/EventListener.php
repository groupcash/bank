<?php
namespace groupcash\bank\app\sourced\command;

interface EventListener {

    /**
     * @param mixed $event
     * @return bool
     */
    public function listensTo($event);

    /**
     * @param mixed $event
     * @return void
     */
    public function on($event);
}