<?php
namespace groupcash\bank\model;

use groupcash\bank\app\Cryptography;
use groupcash\bank\AuthorizeIssuer;
use groupcash\bank\CreateBacker;
use groupcash\bank\EstablishCurrency;
use groupcash\bank\events\BackerCreated;
use groupcash\bank\events\CoinIssued;
use groupcash\bank\events\CoinsRequested;
use groupcash\bank\events\CurrencyEstablished;
use groupcash\bank\events\IssuerAuthorized;
use groupcash\bank\events\RequestCancelled;
use groupcash\bank\IssueCoin;
use groupcash\bank\RequestCoins;
use groupcash\php\Groupcash;
use groupcash\php\model\Output;
use groupcash\php\model\value\Fraction;

class Currency {

    /** @var Groupcash */
    private $lib;

    /** @var Cryptography */
    private $crypto;

    /** @var Authenticator */
    private $auth;

    /** @var bool */
    private $established = false;

    /** @var AccountIdentifier[] */
    private $authorizedIssuers = [];

    /** @var Fraction */
    private $availableSum;

    /** @var BackerIdentifier[] */
    private $createdBackers = [];

    /** @var Fraction[] indexed by account identifier */
    private $activeRequests = [];

    /**
     * @param Groupcash $lib
     * @param Cryptography $crypto
     */
    public function __construct(Groupcash $lib, Cryptography $crypto) {
        $this->lib = $lib;
        $this->crypto = $crypto;
        $this->auth = new Authenticator($crypto, $lib);
        $this->availableSum = new Fraction(0);
    }

    public function handleEstablishCurrency(EstablishCurrency $c) {
        if (!trim($c->getRules())) {
            throw new \Exception("The rules cannot be empty.");
        }

        $key = $this->auth->getKey($c->getCurrency());
        if ($this->established) {
            throw new \Exception("This currency is already established.");
        }

        $currency = CurrencyIdentifier::fromBinary($this->lib->getAddress($key));
        $rules = $this->lib->signRules($key, $c->getRules());

        return new CurrencyEstablished($currency, $rules);
    }

    public function applyCurrencyEstablished() {
        $this->established = true;
    }

    public function handleAuthorizeIssuer(AuthorizeIssuer $c) {
        if (!$this->established) {
            throw new \Exception('Not an established currency.');
        }

        if (in_array($c->getIssuer(), $this->authorizedIssuers)) {
            throw new \Exception('This issuer is already authorized.');
        }

        $currencyKey = $this->auth->getKey($c->getCurrency());
        $currency = CurrencyIdentifier::fromBinary($this->lib->getAddress($currencyKey));
        $issuerAddress = $c->getIssuer()->toBinary();
        $issuer = AccountIdentifier::fromBinary($issuerAddress);

        $authorization = $this->lib->authorizeIssuer($currencyKey, $issuerAddress);


        return new IssuerAuthorized($currency, $issuer, $authorization);
    }

    public function applyIssuerAuthorized(IssuerAuthorized $e) {
        $this->authorizedIssuers[] = $e->getIssuer();
    }

    public function handleCreateBacker(CreateBacker $c) {
        $issuer = AccountIdentifier::fromBinary($this->auth->getAddress($c->getIssuer()));
        if (!in_array($issuer, $this->authorizedIssuers)) {
            throw new \Exception('Not an authorized issuer for this currency.');
        }

        $key = $this->lib->generateKey();
        $backer = BackerIdentifier::fromBinary($this->lib->getAddress($key));

        return new BackerCreated(
            $c->getCurrency(),
            $issuer,
            $backer,
            $key
        );
    }

    public function applyBackerCreated(BackerCreated $e) {
        $this->createdBackers[] = $e->getBacker();
    }

    public function handleIssueCoin(IssueCoin $c) {
        if (!trim($c->getDescription())) {
            throw new \Exception('The description cannot be empty.');
        }

        $issuerKey = $this->auth->getKey($c->getIssuer());
        $issuer = AccountIdentifier::fromBinary($this->lib->getAddress($issuerKey));

        if (!in_array($issuer, $this->authorizedIssuers)) {
            throw new \Exception('Not authorized to issue this currency.');
        }

        return new CoinIssued(
            $c->getCurrency(),
            $issuer,
            $c->getBacker(),
            $this->lib->issueCoin(
                $issuerKey,
                $c->getCurrency()->toBinary(),
                $c->getDescription(),
                new Output(
                    $c->getBacker()->toBinary(),
                    $c->getValue()
                ))
        );
    }

    public function applyCoinIssued(CoinIssued $e) {
        if (in_array($e->getBacker(), $this->createdBackers)) {
            $this->availableSum = $this->availableSum->plus($e->getCoin()->getValue());
        }
    }

    public function handleRequestCoins(RequestCoins $c) {
        $account = AccountIdentifier::fromBinary($this->auth->getAddress($c->getAccount()));
        if (array_key_exists((string)$account, $this->activeRequests)) {
            throw new \Exception('There is already a request from this account for this currency.');
        }
        if ($c->getValue() > $this->availableSum) {
            throw new \Exception('Value exceeds available coins: ' . $this->availableSum);
        }

        return new CoinsRequested(
            $account,
            $c->getCurrency(),
            $c->getValue()
        );
    }

    public function applyCoinsRequested(CoinsRequested $e) {
        $this->availableSum = $this->availableSum->minus($e->getValue());
        $this->activeRequests[(string)$e->getAccount()] = $e->getValue();
    }

    public function applyRequestCancelled(RequestCancelled $e) {
        $this->availableSum = $this->availableSum->plus($this->activeRequests[(string)$e->getAccount()]);
    }
}