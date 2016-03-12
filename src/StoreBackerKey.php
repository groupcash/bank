<?php
namespace groupcash\bank;

use groupcash\bank\app\Command;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\Identifier;
use groupcash\php\model\signing\Binary;

class StoreBackerKey implements Command {

    /** @var BackerIdentifier */
    private $backer;

    /** @var Binary */
    private $key;

    /**
     * @param BackerIdentifier $backer
     * @param Binary $key
     */
    public function __construct(BackerIdentifier $backer, Binary $key) {
        $this->backer = $backer;
        $this->key = $key;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }

    /**
     * @return Binary
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @param Authenticator $auth
     * @return Identifier
     */
    public function getAggregateIdentifier(Authenticator $auth) {
        return $this->backer;
    }
}