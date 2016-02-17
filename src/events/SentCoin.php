<?php
namespace groupcash\bank\events;

use groupcash\php\model\Coin;

class SentCoin {

    /** @var Coin */
    private $coin;

    /** @var Coin */
    private $transferred;

    /** @var Coin */
    private $remaining;

    /**
     * @param Coin $coin
     * @param Coin $transferred
     * @param Coin $remaining
     */
    public function __construct(Coin $coin, Coin $transferred, Coin $remaining) {
        $this->coin = $coin;
        $this->transferred = $transferred;
        $this->remaining = $remaining;
    }

    /**
     * @return Coin
     */
    public function getRemaining() {
        return $this->remaining;
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