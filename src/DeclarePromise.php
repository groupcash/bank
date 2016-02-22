<?php
namespace groupcash\bank;

use groupcash\bank\app\ApplicationCommand;
use groupcash\bank\app\sourced\domain\AggregateIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\Authenticator;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\model\CurrencyIdentifier;

class DeclarePromise implements ApplicationCommand {

    /** @var Authentication */
    private $issuer;

    /** @var BackerIdentifier */
    private $backer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var string */
    private $description;

    /** @var int */
    private $limit;

    /**
     * @param Authentication $issuer
     * @param BackerIdentifier $backer
     * @param CurrencyIdentifier $currency
     * @param string $description
     * @param int $limit
     * @throws \Exception
     */
    public function __construct(Authentication $issuer, BackerIdentifier $backer, CurrencyIdentifier $currency,
                                $description, $limit) {
        $this->issuer = $issuer;
        $this->backer = $backer;
        $this->currency = $currency;
        $this->description = trim($description);
        $this->limit = intval($limit);

        if (!$this->description) {
            throw new \Exception('The promise cannot be empty.');
        }

        if ($this->limit < 1) {
            throw new \Exception('The limit must be positive.');
        }
    }

    /**
     * @return Authentication
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @return BackerIdentifier
     */
    public function getBacker() {
        return $this->backer;
    }

    /**
     * @return CurrencyIdentifier
     */
    public function getCurrency() {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getLimit() {
        return $this->limit;
    }

    /**
     * @param Authenticator $authenticator
     * @return AggregateIdentifier
     */
    public function getAggregateIdentifier(Authenticator $authenticator) {
        return $this->backer;
    }
}