<?php

namespace Apilayer\Currencylayer\Responses;

use DateTimeImmutable;
use DateTimeInterface;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-type _LiveResponseData=array{
 *      success:bool,
 *      terms:string,
 *      privacy:string,
 *      source:Currency::*,
 *      quotes:array<string,float>
 * }
 */
class Live extends DataAbstractResponse
{
    private DateTimeInterface $timestamp;

    /** @psalm-var Currency::* */
    private string $source;

    /** @var array<string,float> */
    private array $quotes;

    /**
     * @param bool $success
     * @param string $terms
     * @param string $privacy
     * @param int $timestamp
     * @param array<string,float> $quotes
     *
     * @psalm-param Currency::* $source
     */
    public function __construct(
        bool $success,
        string $terms,
        string $privacy,
        int $timestamp,
        string $source,
        array $quotes
    ) {
        parent::__construct($success, $terms, $privacy);
        $this->timestamp = (new DateTimeImmutable())->setTimestamp($timestamp);
        $this->source = $source;
        $this->quotes = [];
        foreach ($quotes as $currencyPair => $conversationRate) {
            $this->quotes[$currencyPair] = $conversationRate;
        }
    }

    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }

    /** @psalm-return Currency::* */
    public function getSource(): string
    {
        return $this->source;
    }

    /** @return array<string,float> */
    public function getQuotes(): array
    {
        return $this->quotes;
    }

    /**
     * @psalm-return _LiveResponseData
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess(),
            'terms' => $this->getTerms(),
            'privacy' => $this->getPrivacy(),
            'timestamp' => $this->getTimestamp()->getTimestamp(),
            'source' => $this->getSource(),
            'quotes' => $this->getQuotes(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
