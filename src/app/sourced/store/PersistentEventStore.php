<?php
namespace groupcash\bank\app\sourced\store;

use groupcash\bank\app\sourced\domain\EventStream;
use groupcash\bank\app\sourced\messaging\Identifier;
use watoki\stores\file\FileStore;
use watoki\stores\Store;

class PersistentEventStore implements EventStore {

    /** @var Store */
    private $store;

    public function __construct($dataDir) {
        $this->store = FileStore::forClass(EventStream::class, $dataDir);
    }

    /**
     * @param Identifier $aggregateIdentifier
     * @return EventStream
     */
    public function read(Identifier $aggregateIdentifier) {
        $id = (string)$aggregateIdentifier;
        if (!$this->has($aggregateIdentifier)) {
            $this->store->create(new EventStream(), $id);
        }
        return $this->store->read($id);
    }

    /**
     * @param Identifier $aggregateIdentifier
     * @param EventStream $stream
     */
    public function save(Identifier $aggregateIdentifier, EventStream $stream) {
        $this->store->update($stream);
    }

    /**
     * @param Identifier $aggregateIdentifier
     * @return boolean
     */
    public function has(Identifier $aggregateIdentifier) {
        return $this->store->hasKey((string)$aggregateIdentifier);
    }

    /**
     * @return EventStream
     */
    public function readAll() {
        $all = new EventStream();

        foreach ($this->store->keys() as $key) {
            /** @var EventStream $stream */
            $stream = $this->store->read($key);
            foreach ($stream->getEvents() as $event) {
                $all->add($event);
            }
        }

        return $all;
    }
}