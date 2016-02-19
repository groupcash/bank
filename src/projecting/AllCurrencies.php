<?php
namespace groupcash\bank\projecting;

use groupcash\bank\app\sourced\domain\Projection;
use groupcash\bank\events\CurrencyRegistered;

class AllCurrencies extends Projection {

    /** @var Currency[] */
    private $currencies = [];

    /**
     * @return Currency[]
     */
    public function getCurrencies() {
        usort($this->currencies, function (Currency $a, Currency $b) {
            return strcmp($a->getName(), $b->getName());
        });
        return $this->currencies;
    }

    protected function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->currencies[] = new Currency($e->getCurrency(), $e->getName());
    }
}