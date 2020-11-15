<?php

namespace Apilayer\Tests\Currencylayer\Actions;

use Apilayer\Currencylayer\Actions\ActionInterface;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Currencylayer\Actions\Convert;
use Apilayer\Tests\TestCase;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-import-type _ConvertActionData from \Apilayer\Currencylayer\Actions\Convert
 */
class ConvertTest extends TestCase
{
    public function testGetEndpoint(): void
    {
        $convertAction = new Convert(
            Currency::UAH,
            Currency::BGN,
            1,
            new DateTimeImmutable()
        );

        self::assertEquals(ActionInterface::ENDPOINT_CONVERT, $convertAction->getEndpoint());
    }

    /**
     * @param Convert $convert
     * @return void
     * @dataProvider successGetData
     *
     * @psalm-param _ConvertActionData $expectedResult
     */
    public function testGetDataSuccess(Convert $convert, array $expectedResult): void
    {
        $actualResult = $convert->getData();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * @param float $amount
     * @param DateTimeInterface|null $date
     * @param string $expectedMessage
     * @return void
     * @dataProvider failureGetData
     *
     * @psalm-param Currency::* $fromCurrency
     * @psalm-param Currency::* $toCurrency
     */
    public function testGetDataFailure(
        string $fromCurrency,
        string $toCurrency,
        float $amount,
        ?DateTimeInterface $date,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        new Convert($fromCurrency, $toCurrency, $amount, $date);
    }

    public function successGetData(): Generator
    {
        yield 'default-action' => [
            new Convert(
                Currency::UAH,
                Currency::BGN,
                1,
                new DateTimeImmutable('2020-01-01')
            ),
            [
                'from' => Currency::UAH,
                'to' => Currency::BGN,
                'amount' => 1.0,
                'date' => '2020-01-01',
            ],
        ];

        yield 'without-optional' => [
            new Convert(
                Currency::USD,
                Currency::UAH,
                100,
                null
            ),
            [
                'from' => Currency::USD,
                'to' => Currency::UAH,
                'amount' => 100,
            ],
        ];

        yield 'same-currencies' => [
            new Convert(
                Currency::UAH,
                Currency::UAH,
                100,
                null
            ),
            [
                'from' => Currency::UAH,
                'to' => Currency::UAH,
                'amount' => 100,
            ],
        ];
    }

    public function failureGetData(): Generator
    {
        yield 'wrong-from-currency' => [
            'WRONG',
            Currency::UAH,
            100,
            null,
            '`WRONG` currency is not available.'
        ];

        yield 'wrong-to-currency' => [
            'WRONG',
            Currency::UAH,
            100,
            null,
            '`WRONG` currency is not available.'
        ];

        yield 'wrong-amount' => [
            Currency::USD,
            Currency::UAH,
            -1,
            null,
            'Amount [-1] should be greater than 0.'
        ];
    }
}
