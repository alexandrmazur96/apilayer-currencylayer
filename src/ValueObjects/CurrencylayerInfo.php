<?php

namespace Apilayer\Currencylayer\ValueObjects;

use JsonSerializable;

/**
 * @psalm-immutable
 */
class CurrencylayerInfo implements JsonSerializable
{
    private int $timestamp;
    private float $quote;

    /**
     * @param int $timestamp
     * @param float $quote
     */
    public function __construct(int $timestamp, float $quote)
    {
        $this->timestamp = $timestamp;
        $this->quote = $quote;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return float
     */
    public function getQuote(): float
    {
        return $this->quote;
    }

    /**
     * @psalm-return array{
     *      timestamp:int,
     *      quote:float
     * }
     */
    public function jsonSerialize()
    {
        return [
            'timestamp' => $this->getTimestamp(),
            'quote' => $this->getQuote(),
        ];
    }
}
