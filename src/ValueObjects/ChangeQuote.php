<?php

namespace Apilayer\Currencylayer\ValueObjects;

use JsonSerializable;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-immutable
 */
class ChangeQuote implements JsonSerializable
{
    private string $currencyPair;

    /** @psalm-var Currency::* */
    private string $currencyFrom;
    /** @psalm-var Currency::* */
    private string $currencyTo;
    private ChangeInfo $changeInfo;

    /**
     * @param string $currencyPair
     * @param ChangeInfo $changeInfo
     */
    public function __construct(string $currencyPair, ChangeInfo $changeInfo)
    {
        $this->currencyPair = $currencyPair;
        $this->changeInfo = $changeInfo;
        /** @psalm-var Currency::* currencyFrom */
        $this->currencyFrom = substr($currencyPair, 0, 3);
        /** @psalm-var Currency::* currencyTo */
        $this->currencyTo = substr($currencyPair, 3, 3);
    }

    /**
     * @return string
     */
    public function getCurrencyPair(): string
    {
        return $this->currencyPair;
    }

    /**
     * @return ChangeInfo
     */
    public function getChangeInfo(): ChangeInfo
    {
        return $this->changeInfo;
    }

    /**
     * @psalm-return Currency::*
     */
    public function getCurrencyFrom(): string
    {
        return $this->currencyFrom;
    }

    /**
     * @psalm-return Currency::*
     */
    public function getCurrencyTo(): string
    {
        return $this->currencyTo;
    }

    /**
     * @psalm-return array<string,ChangeInfo>
     */
    public function jsonSerialize()
    {
        return [$this->getCurrencyPair() => $this->getChangeInfo()];
    }
}
