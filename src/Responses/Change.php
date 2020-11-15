<?php

namespace Apilayer\Currencylayer\Responses;

use Apilayer\Currencylayer\ValueObjects\ChangeInfo;
use Apilayer\Currencylayer\ValueObjects\ChangeQuote;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-type _ChangeInfo=array{
 *      start_rate:float,
 *      end_rate:float,
 *      change:float,
 *      change_pct:float
 * }
 *
 * @psalm-type _ChangeResponseData=array{
 *      success:bool,
 *      terms:string,
 *      privacy:string,
 *      change:bool,
 *      start_date:string,
 *      end_date:string,
 *      source:Currency::*,
 *      quotes:list<ChangeQuote>
 * }
 */
class Change extends DataAbstractResponse
{
    private bool $change;
    private DateTimeInterface $startDate;
    private DateTimeInterface $endDate;

    /** @psalm-var Currency::* */
    private string $source;

    /**
     * @var ChangeQuote[]
     * @psalm-var list<ChangeQuote>
     */
    private array $quotes;

    /**
     * @param bool $success
     * @param string $terms
     * @param string $privacy
     * @param bool $change
     * @param string $startDate
     * @param string $endDate
     *
     * @throws Exception
     *
     * @psalm-param Currency::* $source
     * @psalm-param array<string,_ChangeInfo> $quotes
     */
    public function __construct(
        bool $success,
        string $terms,
        string $privacy,
        bool $change,
        string $startDate,
        string $endDate,
        string $source,
        array $quotes
    ) {
        parent::__construct($success, $terms, $privacy);
        $this->change = $change;
        $this->startDate = new DateTimeImmutable($startDate);
        $this->endDate = new DateTimeImmutable($endDate);
        $this->source = $source;
        $this->quotes = [];

        foreach ($quotes as $currencyPair => $changeInfo) {
            $this->quotes[] = new ChangeQuote(
                $currencyPair,
                new ChangeInfo(
                    $changeInfo['start_rate'],
                    $changeInfo['end_rate'],
                    $changeInfo['change'],
                    $changeInfo['change_pct']
                )
            );
        }
    }

    public function isChange(): bool
    {
        return $this->change;
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

    /**
     * @return ChangeQuote[]
     * @psalm-return list<ChangeQuote>
     */
    public function getQuotes(): array
    {
        return $this->quotes;
    }

    /**
     * @psalm-return _ChangeResponseData
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess(),
            'terms' => $this->getTerms(),
            'privacy' => $this->getPrivacy(),
            'change' => $this->isChange(),
            'start_date' => $this->getStartDate()->format('Y-m-d'),
            'end_date' => $this->getEndDate()->format('Y-m-d'),
            'source' => $this->getSource(),
            'quotes' => $this->getQuotes(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
