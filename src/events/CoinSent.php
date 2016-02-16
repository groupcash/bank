<?php
namespace groupcash\bank\events;

use groupcash\bank\app\Event;
use groupcash\php\model\Coin;

class CoinSent extends Event {

    /** @var Coin */
    private $coin;

    /** @var Coin */
    private $transferred;

    /**
     * @param Coin $coin
     * @param Coin $transferred
     */
    public function __construct(Coin $coin, Coin $transferred) {
        parent::__construct();

        $this->coin = $coin;
        $this->transferred = $transferred;
    }

    /**
     * @return Coin
     */
    public function getCoin() {
        return $this->coin;
    }

    /**
     * @return Coin
     */
    public function getTransferred() {
        return $this->transferred;
    }
}