<?php
namespace groupcash\bank\projecting;

use groupcash\bank\app\sourced\domain\Projection;
use groupcash\bank\events\BackerAdded;
use groupcash\bank\model\CurrencyIdentifier;

class CurrencyBackers extends Projection {

    /** @var Backer[] */
    private $backers = [];

    /** @var CurrencyIdentifier */
    private $currency;

    /**
     * @param CurrencyIdentifier $currency
     */
    public function __construct(CurrencyIdentifier $currency) {
        $this->currency = $currency;
    }

    /**
     * @return Backer[]
     */
    public function getBackers() {
        usort($this->backers, function (Backer $a, Backer $b) {
            return strcmp($a->getName(), $b->getName());
        });
        return $this->backers;
    }

    protected function applyBackerAdded(BackerAdded $e) {
        if ($e->getCurrency() == $this->currency) {
            $this->backers[] = new Backer($e->getBacker(), $e->getName());
        }
    }
}