<?php

namespace Apilayer\Currencylayer\Actions;

use Apilayer\Currencylayer\Responses\Factories\ConvertResponseFactory;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Factories\ResponseFactoryInterface;
use DateTimeInterface;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-type _ConvertActionData=array{
 *      from:Currency::*,
 *      to:Currency::*,
 *      amount:float,
 *      date?:string
 * }
 */
class Convert implements ActionInterface
{
    use ActionAssertTrait;

    /** @psalm-var Currency::* */
    private string $fromCurrency;
    /** @psalm-var Currency::* */
    private string $toCurrency;
    private float $amount;

    /** @var DateTimeInterface|null */
    private ?DateTimeInterface $date;

    /**
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $amount
     * @param DateTimeInterface|null $date
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $fromCurrency,
        string $toCurrency,
        float $amount,
        ?DateTimeInterface $date
    ) {
        $this->assertCurrency($fromCurrency);
        $this->assertCurrency($toCurrency);
        $this->assertAmount($amount);

        /**
         * @psalm-var Currency::* $fromCurrency
         * @psalm-var Currency::* $toCurrency
         */

        $this->fromCurrency = $fromCurrency;
        $this->toCurrency = $toCurrency;
        $this->amount = $amount;
        $this->date = $date;
    }

    /** @psalm-return ActionInterface::ENDPOINT_CONVERT */
    public function getEndpoint(): string
    {
        return ActionInterface::ENDPOINT_CONVERT;
    }

    /**
     * @psalm-return _ConvertActionData
     */
    public function getData(): array
    {
        $request = [
            'from' => $this->fromCurrency,
            'to' => $this->toCurrency,
            'amount' => $this->amount,
        ];

        if ($this->date !== null) {
            $request['date'] = $this->date->format('Y-m-d');
        }

        return $request;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return new ConvertResponseFactory();
    }
}
