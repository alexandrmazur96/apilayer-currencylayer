<?php

namespace Apilayer\Currencylayer\Responses;

use DateTimeImmutable;
use Exception;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-type _HistoricalResponseData=array{
 *      success:bool,
 *      terms:string,
 *      privacy:string,
 *      timestamp:int,
 *      historical:bool,
 *      date:string,
 *      source:Currency::*,
 *      quotes:array<string,float>
 * }
 */
class Historical extends DataAbstractResponse
{
    private bool $historical;
    private DateTimeImmutable $date;
    private DateTimeImmutable $timestamp;

    /** @psalm-var Currency::* */
    private string $source;

    /** @var array<string,float> */
    private array $quotes;

    /**
     * @param bool $success
     * @param string $terms
     * @param string $privacy
     * @param bool $historical
     * @param string $date
     * @param int $timestamp
     *
     * @throws Exception
     *
     * @psalm-param Currency::* $source
     * @psalm-param array<string,float> $quotes
     */
    public function __construct(
        bool $success,
        string $terms,
        string $privacy,
        bool $historical,
        string $date,
        int $timestamp,
        string $source,
        array $quotes
    ) {
        parent::__construct($success, $terms, $privacy);
        $this->historical = $historical;
        $this->date = new DateTimeImmutable($date);
        $this->timestamp = (new DateTimeImmutable())->setTimestamp($timestamp);
        $this->source = $source;
        $this->quotes = [];
        foreach ($quotes as $currencyPair => $conversationRate) {
            $this->quotes[$currencyPair] = $conversationRate;
        }
    }

    /**
     * @psalm-return _HistoricalResponseData
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess(),
            'terms' => $this->getTerms(),
            'privacy' => $this->getPrivacy(),
            'timestamp' => $this->getTimestamp()->getTimestamp(),
            'historical' => $this->isHistorical(),
            'date' => $this->getDate()->format('Y-m-d'),
            'source' => $this->getSource(),
            'quotes' => $this->getQuotes(),
        ];
    }

    public function isHistorical(): bool
    {
        return $this->historical;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /** @psalm-return Currency::* */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return array<string,float>
     */
    public function getQuotes(): array
    {
        return $this->quotes;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
