<?php

namespace Apilayer\Currencylayer\ValueObjects;

use JsonSerializable;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-immutable
 */
class Query implements JsonSerializable
{
    /** @psalm-var Currency::* */
    private string $from;
    /** @psalm-var Currency::* */
    private string $to;
    private float $amount;

    /**
     * @psalm-param Currency::* $from
     * @psalm-param Currency::* $to
     * @param float $amount
     */
    public function __construct(string $from, string $to, float $amount)
    {
        $this->from = $from;
        $this->to = $to;
        $this->amount = $amount;
    }

    /**
     * @psalm-return Currency::*
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @psalm-return Currency::*
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @psalm-return array{
     *      from:Currency::*,
     *      to:Currency::*,
     *      amount:float
     * }
     */
    public function jsonSerialize()
    {
        return [
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'amount' => $this->getAmount(),
        ];
    }
}
