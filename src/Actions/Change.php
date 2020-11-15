<?php

namespace Apilayer\Currencylayer\Actions;

use Apilayer\Currencylayer\Responses\Factories\ChangeResponseFactory;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Factories\ResponseFactoryInterface;
use Apilayer\Currencylayer\Enums\Currency;
use DateTimeInterface;

/**
 * @psalm-type _ChangeActionData=array{
 *      start_date:string,
 *      end_date:string,
 *      source?:Currency::*,
 *      currencies?:list<Currency::*>
 * }
 */
class Change implements ActionInterface
{
    use ActionAssertTrait;

    private DateTimeInterface $startDate;
    private DateTimeInterface $endDate;

    /** @psalm-var Currency::*|null */
    private ?string $source;

    /** @psalm-var list<Currency::*>|null */
    private ?array $currencies;

    /**
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @param string|null $source
     * @param string[]|null $currencies
     * @throws InvalidArgumentException
     */
    public function __construct(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        ?string $source,
        ?array $currencies
    ) {
        $this->assertDates($startDate, $endDate);
        if ($source !== null) {
            $this->assertSourceCurrency($source);
            /** @psalm-var Currency::* $source */
        }
        if ($currencies !== null) {
            $this->assertCurrencies($currencies);
            /** @psalm-var list<Currency::*> $currencies */
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->source = $source;
        $this->currencies = $currencies;
    }

    /** @psalm-return ActionInterface::ENDPOINT_CHANGE */
    public function getEndpoint(): string
    {
        return ActionInterface::ENDPOINT_CHANGE;
    }

    /**
     * @psalm-return _ChangeActionData
     */
    public function getData(): array
    {
        $request = [
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
        ];

        if ($this->source !== null) {
            $request['source'] = $this->source;
        }

        if ($this->currencies !== null && !empty($this->currencies)) {
            $request['currencies'] = $this->currencies;
        }

        return $request;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return new ChangeResponseFactory();
    }
}
