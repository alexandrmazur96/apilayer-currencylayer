<?php

namespace Apilayer\Currencylayer\Actions;

use Apilayer\Currencylayer\Enums\Currency;
use DateTimeInterface;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;

trait ActionAssertTrait
{
    /**
     * @param float $amount
     * @throws InvalidArgumentException
     */
    private function assertAmount(float $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException(sprintf('Amount [%s] should be greater than 0.', $amount));
        }
    }

    /**
     * @param string $currency
     * @psalm-assert Currency::* $currency
     * @throws InvalidArgumentException
     */
    private function assertCurrency(string $currency): void
    {
        if (!in_array($currency, Currency::getAvailableCurrencies(), true)) {
            throw new InvalidArgumentException(sprintf("`%s` currency is not available.", $currency));
        }
    }

    /**
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @throws InvalidArgumentException
     */
    private function assertDates(DateTimeInterface $startDate, DateTimeInterface $endDate): void
    {
        if ($startDate > $endDate) {
            throw new InvalidArgumentException(
                sprintf(
                    'Start date [%s] should be lower than or equal to end date [%s].',
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                )
            );
        }
    }

    /**
     * @param string $source
     * @psalm-assert Currency::* $source
     * @throws InvalidArgumentException
     */
    private function assertSourceCurrency(string $source): void
    {
        if (!in_array($source, Currency::getAvailableCurrencies(), true)) {
            throw new InvalidArgumentException(sprintf('$source currency [%s] is not available.', $source));
        }
    }

    /**
     * @param string[] $currencies
     * @psalm-assert list<Currency::*> $currencies
     * @throws InvalidArgumentException
     */
    private function assertCurrencies(array $currencies): void
    {
        if (!empty($currencies)) {
            $diff = array_diff($currencies, Currency::getAvailableCurrencies());
            if (!empty($diff)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Currencies list contains not available values [%s].',
                        join(', ', $diff)
                    )
                );
            }
        }
    }
}
