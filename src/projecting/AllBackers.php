<?php
namespace groupcash\bank\projecting;

use groupcash\bank\app\sourced\domain\Projection;
use groupcash\bank\events\BackerAdded;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class AllBackers extends Projection {

    /** @var string[] */
    private $backerNames = [];

    /** @var CurrencyIdentifier[] */
    private $backerCurrencies = [];

    /** @var Currency[] */
    private $registeredCurrencies = [];

    /**
     * @return Backer[]
     */
    public function getBackers() {
        $backers = [];
        foreach ($this->backerNames as $backer => $name) {
            $currencies = [];
            if (array_key_exists($backer, $this->backerCurrencies)) {
                foreach ($this->backerCurrencies[$backer] as $currency) {
                    if (array_key_exists((string)$currency, $this->registeredCurrencies)) {
                        $currencies[] = $this->registeredCurrencies[(string)$currency];
                    } else {
                        $currencies[] = new Currency($currency);
                    }
                }
            }

            $backers[] = new Backer($currencies, new BackerIdentifier($backer), $name);
        }

        usort($backers, function (Backer $a, Backer $b) {
            return strcmp($a->getName(), $b->getName());
        });
        return $backers;
    }

    protected function applyBackerCreated(BackerCreated $e) {
        $this->backerNames[(string)$e->getBacker()] = $e->getName();
    }

    protected function applyBackerAdded(BackerAdded $e) {
        $this->backerCurrencies[(string)$e->getBacker()][] = $e->getCurrency();
    }

    protected function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->registeredCurrencies[(string)$e->getCurrency()] = new Currency(
            $e->getCurrency(),
            $e->getName()
        );
    }
}