<?php
namespace groupcash\bank\projecting;

use groupcash\bank\app\sourced\domain\Projection;
use groupcash\bank\events\BackerAdded;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\model\CurrencyIdentifier;

class AllBackers extends Projection {

    /** @var Backer[] */
    private $backers = [];

    /** @var null|CurrencyIdentifier */
    private $currency;

    /** @var string[] */
    private $currencyNames = [];

    /**
     * @param null|CurrencyIdentifier $currency
     */
    public function __construct(CurrencyIdentifier $currency = null) {
        $this->currency = $currency;
    }

    /**
     * @return Backer[]
     */
    public function getBackers() {
        usort($this->backers, function (Backer $a, Backer $b) {
            if ($a->getCurrency() == $b->getCurrency()) {
                return strcmp($a->getName(), $b->getName());
            } else {
                return strcmp($a->getCurrencyName(), $b->getCurrencyName());
            }
        });
        return $this->backers;
    }

    protected function applyBackerAdded(BackerAdded $e) {
        if (!$this->currency || $e->getCurrency() == $this->currency) {
            $currencyName = substr((string)$e->getCurrency(), 0, 6);
            if (array_key_exists((string)$e->getCurrency(), $this->currencyNames)) {
                $currencyName = $this->currencyNames[(string)$e->getCurrency()];
            }
            $this->backers[] = new Backer($e->getCurrency(), $currencyName, $e->getBacker(), $e->getName());
        }
    }

    protected function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->currencyNames[(string)$e->getCurrency()] = $e->getName();
    }
}