<?php
namespace groupcash\bank\model;

use groupcash\bank\AddCurrencyToBacker;
use groupcash\bank\DeclarePromise;
use groupcash\bank\events\CoinsIssued;
use groupcash\bank\events\CurrencyAdded;
use groupcash\bank\events\PromiseDeclared;
use groupcash\bank\IssueCoins;

class Backer extends Account {

    /** @var PromiseDeclared[] */
    private $promises = [];

    /** @var CurrencyIdentifier[] */
    private $currencies = [];

    /** @var AccountIdentifier[][] indexed by currencies */
    private $issuers = [];

    /** @var int[] indexed by promise description */
    private $issued = [];

    protected function handleAddCurrencyToBacker(AddCurrencyToBacker $c) {
        $this->record(new CurrencyAdded(
            $c->getBacker(),
            $c->getCurrency(),
            $c->getIssuer()
        ));
    }

    protected function applyCurrencyAdded(CurrencyAdded $e) {
        $this->currencies[] = $e->getCurrency();
        $this->issuers[(string)$e->getCurrency()] = $e->getIssuer();
    }

    protected function handleDeclarePromise(DeclarePromise $c) {
        $issuer = new AccountIdentifier($this->auth->getAddress($c->getIssuer()));

        if (!in_array($c->getCurrency(), $this->currencies)) {
            throw new \Exception('This backer was not added to this currency.');
        }

        if ($this->issuers[(string)$c->getCurrency()] != $issuer) {
            throw new \Exception('This is not an issuer of this currency.');
        }

        $this->record(new PromiseDeclared(
            $c->getBacker(),
            $c->getCurrency(),
            $c->getDescription(),
            $c->getLimit()
        ));
    }

    protected function applyPromiseDeclared(PromiseDeclared $e) {
        $this->promises[] = $e;
        $this->issued[$e->getDescription()] = 0;
    }

    public function handleIssueCoins(IssueCoins $c) {
        $issuer = new AccountIdentifier($this->auth->getAddress($c->getIssuer()));

        if (!in_array($c->getCurrency(), $this->currencies)) {
            throw new \Exception('This backer was not added to this currency.');
        }

        if ($this->issuers[(string)$c->getCurrency()] != $issuer) {
            throw new \Exception('This is not an issuer of this currency.');
        }

        if (!$this->promises) {
            throw new \Exception('This backer has declared no promise.');
        }

        $totalLeft = 0;
        foreach ($this->promises as $promise) {
            $totalLeft += $promise->getLimit() - $this->issued[$promise->getDescription()];
        }

        if ($c->isAll()) {
            $number = $totalLeft;
        } else if ($c->getNumber() <= $totalLeft) {
            $number = $c->getNumber();
        } else {
            throw new \Exception('The requested number exceeds the available limit.');
        }

        $events = [];
        foreach ($this->promises as $promise) {
            $issued = $this->issued[$promise->getDescription()];
            $left = $promise->getLimit() - $issued;

            $issue = min($left, $number);
            $number -= $issue;

            $events[] = new CoinsIssued(
                $c->getCurrency(),
                $c->getBacker(),
                $promise->getDescription(),
                $this->lib->issueCoins(
                    $this->auth->getKey($c->getIssuer()),
                    (string)$c->getCurrency(),
                    (string)$promise->getDescription(),
                    (string)$promise->getBacker(),
                    $issued + 1,
                    $issue));
        }

        foreach ($events as $event) {
            $this->record($event);
        }
    }

    protected function applyCoinsIssued(CoinsIssued $e) {
        $this->issued[$e->getPromise()] += count($e->getCoins());
    }
}