<?php
namespace groupcash\bank\events;

use groupcash\bank\app\Event;
use groupcash\php\model\Coin;

class CoinIssued extends Event {

    /** @var Coin */
    private $coin;

    /**
     * @param Coin $coin
     */
    public function __construct(Coin $coin) {
        parent::__construct();

        $this->coin = $coin;
    }

    /**
     * @return Coin
     */
    public function getCoin() {
        return $this->coin;
    }
}