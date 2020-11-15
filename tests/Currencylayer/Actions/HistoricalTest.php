<?php

namespace Apilayer\Tests\Currencylayer\Actions;

use Apilayer\Currencylayer\Actions\ActionInterface;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Currencylayer\Actions\Historical;
use Apilayer\Tests\TestCase;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-import-type _HistoricalActionData from \Apilayer\Currencylayer\Actions\Historical
 */
class HistoricalTest extends TestCase
{
    public function testGetEndpoint(): void
    {
        $historicalAction = new Historical(new DateTimeImmutable(), null, null);
        self::assertEquals(ActionInterface::ENDPOINT_HISTORICAL, $historicalAction->getEndpoint());
    }

    /**
     * @param Historical $historicalAction
     * @dataProvider successGetData
     *
     * @psalm-param _HistoricalActionData $expectedResult
     */
    public function testGetDataSuccess(Historical $historicalAction, array $expectedResult): void
    {
        $actualResult = $historicalAction->getData();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * @param DateTimeInterface $date
     * @param string $expectedMessage
     * @return void
     * @dataProvider failureGetData
     *
     * @psalm-param Currency::*|null $source
     * @psalm-param list<Currency::*>|null $currencies
     */
    public function testGetDataFailure(
        DateTimeInterface $date,
        ?string $source,
        ?array $currencies,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        new Historical($date, $source, $currencies);
    }

    public function successGetData(): Generator
    {
        yield 'default-action' => [
            new Historical(
                new DateTimeImmutable('2020-01-01'),
                Currency::UAH,
                [Currency::UAH, Currency::USD]
            ),
            [
                'date' => '2020-01-01',
                'source' => Currency::UAH,
                'currencies' => [Currency::UAH, Currency::USD],
            ],
        ];

        yield 'without-optional' => [
            new Historical(
                new DateTimeImmutable('2020-01-01'),
                null,
                null
            ),
            ['date' => '2020-01-01'],
        ];

        yield 'partial-optional-1' => [
            new Historical(
                new DateTimeImmutable('2020-01-01'),
                Currency::UAH,
                null
            ),
            [
                'date' => '2020-01-01',
                'source' => Currency::UAH,
            ],
        ];

        yield 'partial-optional-2' => [
            new Historical(
                new DateTimeImmutable('2020-01-01'),
                null,
                [Currency::UAH, Currency::USD]
            ),
            [
                'date' => '2020-01-01',
                'currencies' => [Currency::UAH, Currency::USD],
            ],
        ];
    }

    public function failureGetData(): Generator
    {
        yield 'wrong-source' => [
            new DateTimeImmutable(),
            'WRONG',
            null,
            '$source currency [WRONG] is not available.'
        ];

        yield 'wrong-currencies-1' => [
            new DateTimeImmutable(),
            null,
            ['WRONG'],
            'Currencies list contains not available values [WRONG].'
        ];

        yield 'wrong-currencies-2' => [
            new DateTimeImmutable(),
            null,
            ['WRONG', Currency::UAH],
            'Currencies list contains not available values [WRONG].'
        ];
    }
}
