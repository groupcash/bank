<?php
namespace groupcash\bank\model;

use groupcash\bank\AddBacker;
use groupcash\bank\app\AggregateRoot;
use groupcash\bank\app\Cryptography;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\DeclarePromise;
use groupcash\bank\DeliverCoin;
use groupcash\bank\events\BackerAdded;
use groupcash\bank\events\CoinDelivered;
use groupcash\bank\events\CoinIssued;
use groupcash\bank\events\CoinSent;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\events\PromiseDeclared;
use groupcash\bank\IssueCoins;
use groupcash\bank\SendCoins;
use groupcash\php\Groupcash;
use groupcash\php\model\Coin;
use groupcash\php\model\Fraction;
use groupcash\php\model\Promise;
use groupcash\php\model\Transference;

class Bank extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /** @var Authenticator */
    private $auth;

    /** @var AccountIdentifier[][] */
    private $issuers = [];

    /** @var AccountIdentifier[][] */
    private $backers = [];

    /** @var PromiseDeclared[][] */
    private $promises = [];

    /** @var int[][] */
    private $issued;

    /** @var Coin[][] */
    private $coins = [];

    /**
     * @param Groupcash $lib
     * @param Cryptography $crypto
     * @param string $secret
     */
    public function __construct(Groupcash $lib, Cryptography $crypto, $secret) {
        $this->lib = $lib;
        $this->auth = new Authenticator($crypto, $secret);
    }

    public function handleAuthorizeIssuer(AuthorizeIssuer $c) {
        $currencyKey = $this->auth->getKey($c->getCurrency());
        $currency = new CurrencyIdentifier($this->lib->getAddress($currencyKey));

        if ($this->contains($this->issuers, $currency, $c->getIssuer())) {
            throw new \Exception('This issuer is already authorized for this currency.');
        }

        $this->record(new IssuerAuthorized(
            $currency,
            $this->lib->authorizeIssuer($currencyKey, (string)$c->getIssuer())
        ));
    }

    protected function applyIssuerAuthorized(IssuerAuthorized $e) {
        $this->issuers[(string)$e->getCurrency()][] = new AccountIdentifier($e->getAuthorization()->getIssuer());
    }

    public function handleAddBacker(AddBacker $c) {
        $this->guardIssuerOfCurrency($c->getIssuer(), $c->getCurrency());

        $key = $this->lib->generateKey();

        $this->record(new BackerAdded(
            $c->getCurrency(),
            new AccountIdentifier($this->lib->getAddress($key)),
            $key
        ));
    }

    protected function applyBackerAdded(BackerAdded $e) {
        $this->backers[(string)$e->getCurrency()][] = $e->getBacker();
    }

    public function handleDeclarePromise(DeclarePromise $c) {
        $this->guardIssuerOfCurrency($c->getIssuer(), $c->getCurrency());
        $this->guardIsBackerOfCurrency($c->getCurrency(), $c->getBacker());

        $this->record(new PromiseDeclared(
            $c->getBacker(),
            $c->getCurrency(),
            $c->getDescription(),
            $c->getLimit()
        ));
    }

    protected function applyPromiseDeclared(PromiseDeclared $e) {
        $this->promises[(string)$e->getCurrency()][(string)$e->getBacker()][] = $e;
    }

    public function handleIssueCoins(IssueCoins $c) {
        $this->guardIssuerOfCurrency($c->getIssuer(), $c->getCurrency());
        $this->guardIsBackerOfCurrency($c->getCurrency(), $c->getBacker());

        /** @var PromiseDeclared[] $promises */
        $promises = $this->get($this->promises, [$c->getCurrency(), $c->getBacker()]);
        if (!$promises) {
            throw new \Exception('This backer has declared no promise.');
        }


        /** @var PromiseDeclared[] $availablePromises */
        $availablePromises = [];
        $available = [];
        $issuedCoins = [];
        foreach ($promises as $promise) {
            $issued = $this->get($this->issued, [$c->getCurrency(), $c->getBacker()], 0);
            $left = $promise->getLimit() - $issued;

            if ($left > 0) {
                $available[] = $left;
                $availablePromises[] = $promise;
                $issuedCoins[] = $issued;
            }
        }

        $number = $c->isAll() ? array_sum($available) : $c->getNumber();
        if ($number > array_sum($available)) {
            throw new \Exception('The requested number exceeds the available limit.');
        }

        foreach ($availablePromises as $i => $promise) {
            $use = min($available[$i], $number);
            $number -= $use;

            $coins = $this->lib->issueCoins(
                $this->auth->getKey($c->getIssuer()),
                (string)$c->getCurrency(),
                (string)$promise->getDescription(),
                (string)$promise->getBacker(),
                $issuedCoins[$i] + 1,
                $use);

            foreach ($coins as $coin) {
                $this->record(new CoinIssued($coin));
            }
        }
    }

    protected function applyCoinIssued(CoinIssued $e) {
        $promise = $this->extractPromise($e->getCoin());

        $currency = $promise->getCurrency();
        $backer = $promise->getBacker();

        $this->issued[$currency][$backer] = $this->get($this->issued, [$currency, $backer], 0) + 1;
    }

    public function handleSendCoins(SendCoins $c) {
        $ownerKey = $this->auth->getKey($c->getOwner());
        $owner = new AccountIdentifier($this->lib->getAddress($ownerKey));
        $coins = $this->get($this->coins, [$c->getCurrency(), $owner], []);

        $total = array_sum(array_map(function ($coinFraction) {
            /** @var Fraction $fraction */
            $fraction = $coinFraction['fraction'];
            return $fraction->toFloat();
        }, $coins));

        if ($total < $c->getAmount()->toFloat()) {
            throw new \Exception('Not sufficient coins of this currency available in account.');
        }

        $sent = new Fraction(0);
        foreach ($coins as $coinFraction) {
            /** @var Coin $coin */
            $coin = $coinFraction['coin'];
            /** @var Fraction $fraction */
            $fraction = $coinFraction['fraction'];

            if ($c->getAmount()->toFloat() < $fraction->toFloat()) {
                $fraction = $c->getAmount();
            }
            $transferFraction = $fraction->dividedBy($coin->getFraction());

            $this->record(new CoinSent(
                $coin,
                $this->lib->transferCoin($ownerKey, $coin, (string)$c->getTarget(), $transferFraction)
            ));

            $sent = $sent->plus($fraction);

            if ($sent->toFloat() >= $c->getAmount()->toFloat()) {
                break;
            }
        }
    }

    function applyCoinSent(CoinSent $e) {
        $owner = new AccountIdentifier($e->getCoin()->getTransaction()->getTarget());
        $currency = new CurrencyIdentifier($this->extractPromise($e->getCoin())->getCurrency());

        $sentFraction = $e->getTransferred()->getFraction();

        $coins = $this->get($this->coins, [$currency, $owner], []);
        foreach ($coins as $i => $coinFraction) {
            /** @var Fraction $fraction */
            $fraction = $coinFraction['fraction'];

            $fractionToBeSent = $sentFraction;
            if ($fraction->toFloat() < $fractionToBeSent->toFloat()) {
                $fractionToBeSent = $fraction;
            }

            $newFraction = $fraction->minus($fractionToBeSent);

            if ($newFraction->toFloat() == 0) {
                unset($coins[$i]);
            } else {
                $coins[$i]['fraction'] = $newFraction;
            }

            $sentFraction = $sentFraction->minus($fractionToBeSent);
            if ($sentFraction->toFloat() == 0) {
                break;
            }
        }

        $this->coins[(string)$currency][(string)$owner] = $coins;
    }

    public function handleDeliverCoin(DeliverCoin $c) {
        $this->record(new CoinDelivered($c->getCoin()));
    }

    protected function applyCoinDelivered(CoinDelivered $e) {
        $coin = $e->getCoin();
        $promise = $this->extractPromise($coin);
        $target = $coin->getTransaction()->getTarget();

        $this->coins[$promise->getCurrency()][$target][] = [
            'coin' => $coin,
            'fraction' => $coin->getFraction()
        ];
    }

    private function guardIssuerOfCurrency($authentication, $currency) {
        $issuer = new AccountIdentifier($this->lib->getAddress($this->auth->getKey($authentication)));

        if (!$this->contains($this->issuers, $currency, $issuer)) {
            throw new \Exception('This is not an issuer of this currency.');
        }
    }

    private function guardIsBackerOfCurrency($currency, $backer) {
        if (!$this->contains($this->backers, $currency, $backer)) {
            throw new \Exception('This backer is not registered with this currency.');
        }
    }

    private function contains(&$array, $key, $element) {
        if (!isset($array[(string)$key])) {
            return false;
        }
        return in_array($element, $array[(string)$key]);
    }

    private function get($array, $keys, $default = null) {
        foreach ($keys as $key) {
            if (!isset($array[(string)$key])) {
                return $default;
            }
            $array = $array[(string)$key];
        }
        return $array;
    }

    /**
     * @param Coin $coin
     * @return Promise
     * @throws \Exception
     */
    private function extractPromise(Coin $coin) {
        $transaction = $coin->getTransaction();
        while ($transaction instanceof Transference) {
            $transaction = $transaction->getCoin()->getTransaction();
        }

        if ($transaction instanceof Promise) {
            return $transaction;
        }

        throw new \Exception('Could not analyze coin');
    }
}