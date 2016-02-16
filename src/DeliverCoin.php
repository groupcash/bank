<?php
namespace groupcash\bank;

use groupcash\php\model\Coin;

class DeliverCoin {

    /** @var Coin */
    private $coin;

    /**
     * @param Coin $coin
     */
    public function __construct(Coin $coin) {
        $this->coin = $coin;
    }

    /**
     * @return Coin
     */
    public function getCoin() {
        return $this->coin;
    }
}