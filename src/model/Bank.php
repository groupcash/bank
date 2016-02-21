<?php
namespace groupcash\bank\model;

use groupcash\bank\AddBacker;
use groupcash\bank\AddExistingBacker;
use groupcash\bank\app\sourced\domain\AggregateRoot;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\CreateAccount;
use groupcash\bank\DeclarePromise;
use groupcash\bank\DeliverCoins;
use groupcash\bank\DepositCoins;
use groupcash\bank\events\BackerAdded;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\CoinsDelivered;
use groupcash\bank\events\CoinsIssued;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\events\CoinsWithdrawn;
use groupcash\bank\events\CurrencyRegistered;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\events\PromiseDeclared;
use groupcash\bank\events\TransferredCoin;
use groupcash\bank\IssueCoins;
use groupcash\bank\RegisterCurrency;
use groupcash\bank\SendCoins;
use groupcash\bank\WithdrawCoins;
use groupcash\php\Groupcash;
use groupcash\php\model\Authorization;
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

    /** @var string[] Private keys indexed by backer identifier */
    private $backerKeys = [];

    /** @var string[] Names indexed by backer identifier */
    private $backerNames = [];

    /** @var Authorization[][] indexed by currency */
    private $authorizations = [];

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

        return new CreatedAccount($this->lib->getAddress($key), $this->auth->encrypt($key, $c->getPassword()));
    }

    public function handleRegisterCurrency(RegisterCurrency $c) {
        if (array_key_exists($c->getName(), $this->currencies)) {
            throw new \Exception('A currency with this name is already registered.');
        }

        $currency = new CurrencyIdentifier((string)$c->getCurrency());

        if (in_array($currency, $this->currencies)) {
            throw new \Exception('This currency is already registered.');
        }

        $this->record(new CurrencyRegistered(
            $currency,
            $c->getName()));
    }

    protected function applyCurrencyRegistered(CurrencyRegistered $e) {
        $this->currencies[$e->getName()] = $e->getCurrency();
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
        $this->authorizations[(string)$e->getCurrency()][] = $e->getAuthorization();
    }

    public function handleAddBacker(AddBacker $c) {
        $this->guardIssuerOfCurrency($c->getIssuer(), $c->getCurrency());

        $key = $this->lib->generateKey();
        $address = $this->lib->getAddress($key);

        $this->record(new BackerCreated(
            $c->getCurrency(),
            new BackerIdentifier($address),
            $key,
            $c->getName()
        ));

        $this->record(new BackerAdded(
            $c->getCurrency(),
            new BackerIdentifier($address)
        ));

        return new CreatedAccount($address);
    }

    protected function applyBackerCreated(BackerCreated $e) {
        $this->backerKeys[(string)$e->getBacker()] = $e->getBackerKey();
        $this->backerNames[(string)$e->getBacker()] = $e->getName();
    }

    protected function handleAddExistingBacker(AddExistingBacker $c) {
        $this->guardIssuerOfCurrency($c->getIssuer(), $c->getCurrency());

        if (!$this->get($this->backers, [$c->getBacker()], null)) {
            throw new \Exception('This backer does not exist.');
        }

        if ($this->contains($this->backers, $c->getBacker(), $c->getCurrency())) {
            throw new \Exception('This backer was already added to this currency.');
        }

        $this->record(new BackerAdded(
            $c->getCurrency(),
            $c->getBacker()
        ));
    }

    protected function applyBackerAdded(BackerAdded $e) {
        $this->backers[(string)$e->getBacker()][] = $e->getCurrency();
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

        $this->record(new CoinsSent(
            $c->getCurrency(),
            $owner,
            $c->getTarget(),
            $this->collectCoins($c->getCurrency(), $owner, $ownerKey, $c->getAmount(), $c->getTarget()),
            $c->getSubject()));
    }

    protected function applyCoinsSent(CoinsSent $e) {
        $this->subtractCoins($e->getCurrency(), $e->getOwner(), $e->getSentCoins());
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

    protected function handleWithdrawCoins(WithdrawCoins $c) {
        $ownerKey = $this->auth->getKey($c->getAccount());
        $owner = new AccountIdentifier($this->lib->getAddress($ownerKey));

        $collected = $this->collectCoins($c->getCurrency(), $owner, $ownerKey, $c->getAmount(), $owner);

        $this->record(new CoinsWithdrawn(
            $c->getCurrency(),
            $owner,
            $collected
        ));

        return array_map(function (TransferredCoin $coin) {
            return $coin->getTransferred();
        }, $collected);
    }

    protected function applyCoinsWithdrawn(CoinsWithdrawn $e) {
        $this->subtractCoins($e->getCurrency(), $e->getAccount(), $e->getCoins());
    }

    protected function handleDepositCoins(DepositCoins $c) {
        $account = $this->lib->getAddress($this->auth->getKey($c->getAccount()));

        $depositedCoins = [];
        foreach ($c->getCoins() as $i => $coin) {
            $number = $i + 1;

            if ($coin->getTransaction()->getTarget() != $account) {
                throw new \Exception("Coin $number does not belong to account.");
            } else if ($this->hasInAccount($coin)) {
                throw new \Exception("Coin $number is already in account.");
            }

            try {
                $validated = $this->validateCoin($coin);
            } catch (\Exception $e) {
                throw new \Exception("Could not validate coin $number: " . $e->getMessage());
            }

            $currency = $this->extractPromise($coin)->getCurrency();
            $depositedCoins[$currency][] = $validated;
        }

        foreach ($depositedCoins as $currency => $coins) {
            $this->record(new CoinsDelivered(
                new CurrencyIdentifier($currency),
                new AccountIdentifier($account),
                $coins,
                'Deposited'
            ));
        }
    }

    private function hasInAccount(Coin $coin) {
        $account = $coin->getTransaction()->getTarget();
        $promise = $this->extractPromise($coin);
        $currency = $promise->getCurrency();

        $coinsInAccount = $this->get($this->coins, [$currency, $account], []);
        return in_array($coin, $coinsInAccount);
    }

    private function validateCoin(Coin $coin) {
        $promise = $this->extractPromise($coin);
        $backer = $promise->getBacker();
        $currency = $promise->getCurrency();

        $this->guardIsBackerOfCurrency(new CurrencyIdentifier($currency), new BackerIdentifier($backer));

        if (!$this->lib->verifyCoin($coin, $this->get($this->authorizations, [$currency], null))) {
            throw new \Exception('Coin could not be verified.');
        }

        $backerKey = $this->backerKeys[$backer];
        return $this->lib->validateCoin($backerKey, $coin);
    }

    private function extractPromise(Coin $coin) {
        /** @var Promise|Transference $promise */
        $promise = $coin->getTransaction();
        while ($promise instanceof Transference) {
            $promise = $promise->getCoin()->getTransaction();
        }

        if ($promise instanceof Promise) {
            return $promise;
        }

        throw new \Exception('Invalid coin.');
    }

    private function collectCoins(CurrencyIdentifier $currency, AccountIdentifier $owner, $ownerKey, Fraction $amount, AccountIdentifier $target) {
        /** @var Coin[] $coins */
        $coins = $this->get($this->coins, [$currency, $owner], []);

        $collected = [];
        $left = $amount;
        foreach ($coins as $coin) {
            $fraction = $coin->getFraction();

            if ($left->toFloat() < $fraction->toFloat()) {
                $fraction = $left;
            }
            $transferFraction = $fraction->dividedBy($coin->getFraction());
            $remainFraction = (new Fraction(1))->minus($transferFraction);

            $collected[] = new TransferredCoin(
                $coin,
                $this->validateCoin($this->lib->transferCoin($ownerKey, $coin, (string)$target, $transferFraction)),
                $this->validateCoin($this->lib->transferCoin($ownerKey, $coin, (string)$owner, $remainFraction))
            );

            $left = $left->minus($fraction);
            if ($left == new Fraction(0)) {
                return $collected;
            }
        }

        throw new \Exception('Not sufficient coins of this currency available in account.');
    }

    /**
     * @param CurrencyIdentifier $currency
     * @param AccountIdentifier $owner
     * @param TransferredCoin[] $transferredCoins
     */
    private function subtractCoins($currency, $owner, $transferredCoins) {
        $coins = $this->get($this->coins, [$currency, $owner], []);

        foreach ($transferredCoins as $transferredCoin) {
            if ($transferredCoin->getRemaining()->getFraction() == new Fraction(0)) {
                $replacement = [];
            } else {
                $replacement = [$transferredCoin->getRemaining()];
            }

            array_splice($coins, array_search($transferredCoin->getCoin(), $coins), 1, $replacement);
        }

        $this->coins[(string)$currency][(string)$owner] = $coins;
    }

    private function guardIssuerOfCurrency($authentication, $currency) {
        $issuer = new AccountIdentifier($this->lib->getAddress($this->auth->getKey($authentication)));

        if (!$this->contains($this->issuers, $currency, $issuer)) {
            throw new \Exception('This is not an issuer of this currency.');
        }
    }

    private function guardIsBackerOfCurrency(CurrencyIdentifier $currency, BackerIdentifier $backer) {
        if (!$this->contains($this->backers, $backer, $currency)) {
            throw new \Exception('This backer was not added to this currency.');
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