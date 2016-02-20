<?php
namespace groupcash\bank\model;

use groupcash\bank\AddBacker;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\CreateAccount;
use groupcash\bank\DeclarePromise;
use groupcash\bank\DeliverCoins;
use groupcash\bank\events\BackerAdded;
use groupcash\bank\events\CoinsDelivered;
use groupcash\bank\events\CoinsIssued;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\events\PromiseDeclared;
use groupcash\bank\events\SentCoin;
use groupcash\bank\IssueCoins;
use groupcash\bank\RegisterCurrency;
use groupcash\bank\SendCoins;
use groupcash\php\Groupcash;
use groupcash\php\model\Coin;
use groupcash\php\model\Fraction;

class Bank extends AggregateRoot {

    /** @var Groupcash */
    private $lib;

    /** @var Authenticator */
    private $auth;

    /** @var AccountIdentifier[][] */
    private $issuers = [];

    /** @var BackerIdentifier[][] */
    private $backers = [];

    /** @var PromiseDeclared[][] */
    private $promises = [];

    /** @var int[][] */
    private $issued;

    /** @var Coin[][] */
    private $coins = [];

    /** @var CurrencyIdentifier[] */
    private $currencies = [];

    /**
     * @param Groupcash $lib
     * @param Authenticator $auth
     */
    public function __construct(Groupcash $lib, Authenticator $auth) {
        $this->lib = $lib;
        $this->auth = $auth;
    }

    public function handleCreateAccount(CreateAccount $c) {
        $key = $this->lib->generateKey();

        return [
            'key' => $this->auth->encrypt($key, $c->getPassword()),
            'address' => $this->lib->getAddress($key)
        ];
    }

    public function handleRegisterCurrency(RegisterCurrency $c) {
        if (array_key_exists($c->getName(), $this->currencies)) {
            throw new \Exception('A currency with this name is already registered.');
        }
        $this->record(new CurrencyRegistered(
            new CurrencyIdentifier((string)$c->getCurrency()),
            $c->getName()));
    }

    protected function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->currencies[$e->getName()] = new CurrencyIdentifier((string)$e->getCurrency());
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
            new BackerIdentifier($this->lib->getAddress($key)),
            $key,
            $c->getName()
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

            $this->record(new CoinsIssued(
                $c->getCurrency(),
                $c->getBacker(),
                $this->lib->issueCoins(
                    $this->auth->getKey($c->getIssuer()),
                    (string)$c->getCurrency(),
                    (string)$promise->getDescription(),
                    (string)$promise->getBacker(),
                    $issuedCoins[$i] + 1,
                    $use)));
        }
    }

    protected function applyCoinsIssued(CoinsIssued $e) {
        $this->issued[(string)$e->getCurrency()][(string)$e->getBacker()] =
            $this->get($this->issued, [$e->getCurrency(), $e->getBacker()], 0) + count($e->getCoins());
    }

    public function handleSendCoins(SendCoins $c) {
        $ownerKey = $this->auth->getKey($c->getOwner());
        $owner = new AccountIdentifier($this->lib->getAddress($ownerKey));

        /** @var Coin[] $coins */
        $coins = $this->get($this->coins, [$c->getCurrency(), $owner], []);

        $sent = [];
        $left = $c->getFraction();
        foreach ($coins as $coin) {
            $fraction = $coin->getFraction();

            if ($left->toFloat() < $fraction->toFloat()) {
                $fraction = $left;
            }
            $transferFraction = $fraction->dividedBy($coin->getFraction());
            $remainFraction = (new Fraction(1))->minus($transferFraction);

            $sent[] = new SentCoin(
                $coin,
                $this->lib->transferCoin($ownerKey, $coin, (string)$c->getTarget(), $transferFraction),
                $this->lib->transferCoin($ownerKey, $coin, (string)$owner, $remainFraction)
            );

            $left = $left->minus($fraction);
            if ($left == new Fraction(0)) {
                $this->record(new CoinsSent(
                    $c->getCurrency(),
                    $owner,
                    $c->getTarget(),
                    $sent,
                    $c->getSubject()));
                return;
            }
        }

        throw new \Exception('Not sufficient coins of this currency available in account.');
    }

    function applyCoinsSent(CoinsSent $e) {
        $owner = $e->getOwner();
        $currency = $e->getCurrency();

        $coins = $this->get($this->coins, [$currency, $owner], []);

        foreach ($e->getSentCoins() as $sentCoin) {
            if ($sentCoin->getRemaining()->getFraction() == new Fraction(0)) {
                $replacement = [];
            } else {
                $replacement = [$sentCoin->getRemaining()];
            }

            array_splice($coins, array_search($sentCoin->getCoin(), $coins), 1, $replacement);
        }

        $this->coins[(string)$currency][(string)$owner] = $coins;
    }

    public function handleDeliverCoins(DeliverCoins $c) {
        $this->record(new CoinsDelivered(
            $c->getCurrency(),
            $c->getTarget(),
            $c->getCoins(),
            $c->getSubject()));
    }

    protected function applyCoinsDelivered(CoinsDelivered $e) {
        foreach ($e->getCoins() as $coin) {
            $this->coins[(string)$e->getCurrency()][(string)$e->getTarget()][] = $coin;
        }
    }

    private function guardIssuerOfCurrency($authentication, $currency) {
        $issuer = new AccountIdentifier($this->lib->getAddress($this->auth->getKey($authentication)));

        if (!$this->contains($this->issuers, $currency, $issuer)) {
            throw new \Exception('This is not an issuer of this currency.');
        }
    }

    private function guardIsBackerOfCurrency(CurrencyIdentifier $currency, BackerIdentifier $backer) {
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
}