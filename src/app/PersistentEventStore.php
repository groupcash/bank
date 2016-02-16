<?php
namespace groupcash\bank\app;

use groupcash\bank\model\Identifier;
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
}