<?php

namespace Apilayer\Currencylayer\Responses;

use Apilayer\Currencylayer\ValueObjects\CurrencylayerInfo;
use Apilayer\Currencylayer\ValueObjects\Query;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-type _Query=array{
 *      from:Currency::*,
 *      to:Currency::*,
 *      amount:float
 * }
 *
 * @psalm-type _Info=array{
 *      timestamp:int,
 *      quote:float
 * }
 *
 * @psalm-type _ConvertResponseData=array{
 *      success:bool,
 *      terms:string,
 *      privacy:string,
 *      query:Query,
 *      info:CurrencylayerInfo,
 *      result:float,
 *      historical?:bool,
 *      date?:string
 * }
 */
class Convert extends DataAbstractResponse
{
    private Query $query;
    private CurrencylayerInfo $info;
    private float $result;
    private ?bool $historical;
    private ?DateTimeInterface $date;

    /**
     * @param bool $success
     * @param string $terms
     * @param string $privacy
     * @param float $result
     * @param ?bool $historical
     * @param ?string $date
     *
     * @throws Exception
     *
     * @psalm-param _Query $query
     * @psalm-param _Info $info
     */
    public function __construct(
        bool $success,
        string $terms,
        string $privacy,
        array $query,
        array $info,
        float $result,
        ?bool $historical,
        ?string $date
    ) {
        parent::__construct($success, $terms, $privacy);
        $this->query = new Query($query['from'], $query['to'], (float)$query['amount']);
        $this->info = new CurrencylayerInfo($info['timestamp'], $info['quote']);
        $this->result = $result;
        $this->historical = $historical;

        if ($date !== null) {
            $this->date = new DateTimeImmutable($date);
        } else {
            $this->date = null;
        }
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    public function getInfo(): CurrencylayerInfo
    {
        return $this->info;
    }

    public function getResult(): float
    {
        return $this->result;
    }

    public function getHistorical(): ?bool
    {
        return $this->historical;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @psalm-return _ConvertResponseData
     */
    public function toArray(): array
    {
        $result = [
            'success' => $this->isSuccess(),
            'terms' => $this->getTerms(),
            'privacy' => $this->getPrivacy(),
            'query' => $this->getQuery(),
            'info' => $this->getInfo(),
            'result' => $this->getResult(),
        ];

        $historical = $this->getHistorical();
        if ($historical !== null) {
            $result['historical'] = $historical;
        }

        $date = $this->getDate();
        if ($date !== null) {
            $result['date'] = $date->format('Y-m-d');
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
