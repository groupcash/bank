<?php
namespace groupcash\bank\app\sourced\stores;

use watoki\stores\stores\MemoryStore;

class MemoryEventStore extends StoringEventStore {

    public function __construct() {
        parent::__construct(new MemoryStore());
    }
}