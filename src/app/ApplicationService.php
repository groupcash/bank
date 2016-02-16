<?php
namespace groupcash\bank\app;

use groupcash\bank\DeliverCoin;
use groupcash\bank\events\CoinIssued;
use groupcash\bank\events\CoinSent;
use groupcash\bank\model\Bank;
use groupcash\bank\model\BankIdentifier;
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

    public function handle($command) {
        $aggregateIdentifier = BankIdentifier::singleton();

        $stream = $this->store->read($aggregateIdentifier);

        $aggregate = $this->getAggregate();
        $aggregate->reconstitute($stream);

        $handleMethod = 'handle' . (new \ReflectionClass($command))->getShortName();
        $returned = call_user_func([$aggregate, $handleMethod], $command);
        
        $this->store->save($aggregateIdentifier, $stream);

        foreach ($aggregate->getRecordedEvents() as $event) {
            $stream->add($event);

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

    protected function onCoinIssued(CoinIssued $e) {
        $this->handle(new DeliverCoin($e->getCoin()));
    }

    protected function onCoinSent(CoinSent $e) {
        $this->handle(new DeliverCoin($e->getTransferred()));
    }
}