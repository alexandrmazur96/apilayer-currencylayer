<?php

namespace Apilayer\Currencylayer\Responses;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-type _TimeframeResponseData=array{
 *      success:bool,
 *      terms:string,
 *      privacy:string,
 *      timeframe:bool,
 *      start_date:string,
 *      end_date:string,
 *      source:Currency::*,
 *      quotes:array<string,array<string,float>>
 * }
 */
class Timeframe extends DataAbstractResponse
{
    private bool $timeframe;
    private DateTimeInterface $startDate;
    private DateTimeInterface $endDate;

    /** @psalm-var Currency::* */
    private string $source;

    /** @var array<string,array<string,float>> */
    private array $quotes;

    /**
     * @param bool $success
     * @param string $terms
     * @param string $privacy
     * @param bool $timeframe
     * @param string $startDate
     * @param string $endDate
     * @param array<string,array<string,float>> $quotes
     *
     * @throws Exception
     *
     * @psalm-param Currency::* $source
     */
    public function __construct(
        bool $success,
        string $terms,
        string $privacy,
        bool $timeframe,
        string $startDate,
        string $endDate,
        string $source,
        array $quotes
    ) {
        parent::__construct($success, $terms, $privacy);
        $this->timeframe = $timeframe;
        $this->startDate = new DateTimeImmutable($startDate);
        $this->endDate = new DateTimeImmutable($endDate);
        $this->source = $source;
        $this->quotes = [];
        $liveQuotesList = [];
        foreach ($quotes as $date => $liveQuotes) {
            foreach ($liveQuotes as $currencyPair => $conversationRate) {
                $liveQuotesList[$currencyPair] = $conversationRate;
            }
            $this->quotes[$date] = $liveQuotesList;
            $liveQuotesList = [];
        }
    }

    public function isTimeframe(): bool
    {
        return $this->timeframe;
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeInterface
    {
        return $this->endDate;
    }

    /** @psalm-return Currency::* */
    public function getSource(): string
    {
        return $this->source;
    }

    /** @return array<string,array<string,float>> */
    public function getQuotes(): array
    {
        return $this->quotes;
    }

    /**
     * @psalm-return _TimeframeResponseData
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess(),
            'terms' => $this->getTerms(),
            'privacy' => $this->getPrivacy(),
            'timeframe' => $this->isTimeframe(),
            'start_date' => $this->getStartDate()->format('Y-m-d'),
            'end_date' => $this->getEndDate()->format('Y-m-d'),
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
