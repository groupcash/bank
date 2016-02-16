<?php
namespace groupcash\bank;

use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\Authentication;
use groupcash\bank\model\CurrencyIdentifier;

class DeclarePromise {

    /** @var Authentication */
    private $issuer;

    /** @var AccountIdentifier */
    private $backer;

    /** @var CurrencyIdentifier */
    private $currency;

    /** @var string */
    private $description;

    /** @var int */
    private $limit;

    /**
     * @param Authentication $issuer
     * @param AccountIdentifier $backer
     * @param CurrencyIdentifier $currency
     * @param string $description
     * @param int $limit
     * @throws \Exception
     */
    public function __construct(Authentication $issuer, AccountIdentifier $backer, CurrencyIdentifier $currency,
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
     * @return AccountIdentifier
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
}