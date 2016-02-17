<?php
namespace groupcash\bank\app;

use groupcash\bank\DeliverCoins;
use groupcash\bank\events\CoinsIssued;
use groupcash\bank\events\CoinsSent;
use groupcash\bank\events\SentCoin;
use groupcash\bank\ListTransactions;
use groupcash\bank\model\Bank;
use groupcash\bank\model\BankIdentifier;
use groupcash\bank\projecting\TransactionHistory;
use groupcash\php\Groupcash;

class ApplicationService {

    /** @var Groupcash */
    private $lib;

    /** @var EventStore */
    private $store;

    /** @var Cryptography */
    private $crypto;

    /** @var string */
    private $secret;

    /**
     * @param EventStore $store
     * @param Cryptography $crypto
     * @param Groupcash $lib
     * @param string $secret
     */
    public function __construct(EventStore $store, Cryptography $crypto, Groupcash $lib, $secret) {
        $this->lib = $lib;
        $this->store = $store;
        $this->crypto = $crypto;
        $this->secret = $secret;
    }

    public function execute($query) {
        if ($query instanceof ListTransactions) {
            $stream = $this->store->read(BankIdentifier::singleton());
            return new TransactionHistory($stream, $query, $this->lib, $this->crypto, $this->secret);
        }

        throw new \Exception('Cannot execute unkown query.');
    }

    public function handle($command) {
        $aggregateIdentifier = BankIdentifier::singleton();

        $stream = $this->store->read($aggregateIdentifier);

        $aggregate = $this->getAggregate();
        $aggregate->reconstitute($stream);

        $handleMethod = 'handle' . (new \ReflectionClass($command))->getShortName();
        $returned = call_user_func([$aggregate, $handleMethod], $command);

        foreach ($aggregate->getRecordedEvents() as $event) {
            $stream->add($event);
        }
        $this->store->save($aggregateIdentifier, $stream);

        foreach ($aggregate->getRecordedEvents() as $event) {
            $onMethod = 'on' . (new \ReflectionClass($event))->getShortName();
            if (method_exists($this, $onMethod)) {
                call_user_func([$this, $onMethod], $event);
            }
        }
        return $returned;
    }

    /**
     * @return AggregateRoot
     */
    private function getAggregate() {
        return new Bank($this->lib, $this->crypto, $this->secret);
    }

    protected function onCoinsIssued(CoinsIssued $e) {
        $this->handle(new DeliverCoins($e->getCurrency(), $e->getBacker(), $e->getCoins(), 'Issued'));
    }

    protected function onCoinsSent(CoinsSent $e) {
        $this->handle(new DeliverCoins(
            $e->getCurrency(),
            $e->getTarget(),
            array_map(function (SentCoin $sentCoin) {
                return $sentCoin->getTransferred();
            }, $e->getSentCoins()),
            $e->getSubject()));
    }
}