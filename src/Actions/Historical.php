<?php

namespace Apilayer\Currencylayer\Actions;

use Apilayer\Currencylayer\Responses\Factories\HistoricalResponseFactory;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Factories\ResponseFactoryInterface;
use Apilayer\Currencylayer\Enums\Currency;
use DateTimeInterface;

/**
 * @psalm-type _HistoricalActionData=array{
 *      date:string,
 *      source?:Currency::*,
 *      currencies?:list<Currency::*>
 * }
 */
class Historical implements ActionInterface
{
    use ActionAssertTrait;

    private DateTimeInterface $date;

    /** @psalm-var Currency::*|null */
    private ?string $source;

    /** @psalm-var list<Currency::*>|null */
    private ?array $currencies;

    /**
     * @param DateTimeInterface $date
     * @param ?string $source
     * @param ?string[] $currencies
     * @throws InvalidArgumentException
     */
    public function __construct(
        DateTimeInterface $date,
        ?string $source = null,
        ?array $currencies = null
    ) {
        if ($source !== null) {
            $this->assertSourceCurrency($source);
            /** @psalm-var Currency::* $source */
        }
        if ($currencies !== null) {
            $this->assertCurrencies($currencies);
            /** @psalm-var list<Currency::*> $currencies */
        }

        $this->date = $date;
        $this->source = $source;
        $this->currencies = $currencies;
    }

    /** @psalm-return ActionInterface::ENDPOINT_HISTORICAL */
    public function getEndpoint(): string
    {
        return ActionInterface::ENDPOINT_HISTORICAL;
    }

    /**
     * @psalm-return _HistoricalActionData
     */
    public function getData(): array
    {
        $request = [
            'date' => $this->date->format('Y-m-d'),
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
        return new HistoricalResponseFactory();
    }
}
