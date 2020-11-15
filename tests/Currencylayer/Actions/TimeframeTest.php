<?php

namespace Apilayer\Tests\Currencylayer\Actions;

use Apilayer\Currencylayer\Actions\ActionInterface;
use Apilayer\Currencylayer\Actions\Timeframe;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-import-type _TimeframeActionData from \Apilayer\Currencylayer\Actions\Timeframe
 */
class TimeframeTest extends TestCase
{
    public function testGetEndpoint(): void
    {
        $timeframe = new Timeframe(
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            Currency::UAH,
            [Currency::UAH, Currency::USD]
        );
        self::assertEquals(ActionInterface::ENDPOINT_TIMEFRAME, $timeframe->getEndpoint());
    }

    /**
     * @param Timeframe $timeframeAction
     * @return void
     * @dataProvider successGetData
     *
     * @psalm-param _TimeframeActionData $expectedResult
     */
    public function testGetDataSuccess(Timeframe $timeframeAction, array $expectedResult): void
    {
        $actualResult = $timeframeAction->getData();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @param string $expectedMessage
     * @return void
     * @dataProvider failureGetData
     *
     * @psalm-param Currency::*|null $source
     * @psalm-param list<Currency::*>|null $currencies
     */
    public function testGetDataFailure(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        ?string $source,
        ?array $currencies,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        new Timeframe($startDate, $endDate, $source, $currencies);
    }

    public function successGetData(): Generator
    {
        yield 'default-action' => [
            new Timeframe(
                new DateTimeImmutable('2020-01-01'),
                new DateTimeImmutable('2020-02-01'),
                Currency::UAH,
                [Currency::UAH, Currency::USD]
            ),
            [
                'start_date' => '2020-01-01',
                'end_date' => '2020-02-01',
                'source' => Currency::UAH,
                'currencies' => [Currency::UAH, Currency::USD],
            ],
        ];

        yield 'without-optional' => [
            new Timeframe(
                new DateTimeImmutable('2020-01-01'),
                new DateTimeImmutable('2020-02-01'),
                null,
                null
            ),
            [
                'start_date' => '2020-01-01',
                'end_date' => '2020-02-01',
            ],
        ];

        yield 'partial-optional-1' => [
            new Timeframe(
                new DateTimeImmutable('2020-01-01'),
                new DateTimeImmutable('2020-02-01'),
                Currency::UAH,
                null
            ),
            [
                'start_date' => '2020-01-01',
                'end_date' => '2020-02-01',
                'source' => Currency::UAH,
            ],
        ];

        yield 'partial-optional-2' => [
            new Timeframe(
                new DateTimeImmutable('2020-01-01'),
                new DateTimeImmutable('2020-02-01'),
                null,
                [Currency::UAH, Currency::USD]
            ),
            [
                'start_date' => '2020-01-01',
                'end_date' => '2020-02-01',
                'currencies' => [Currency::UAH, Currency::USD],
            ],
        ];

        yield 'equal-dates' => [
            new Timeframe(
                new DateTimeImmutable('2020-01-01'),
                new DateTimeImmutable('2020-01-01'),
                null,
                null
            ),
            [
                'start_date' => '2020-01-01',
                'end_date' => '2020-01-01',
            ],
        ];

        /** @var DateTimeInterface $startDate */
        $startDate = DateTimeImmutable::createFromFormat('U', '-1');
        /** @var DateTimeInterface $endDate */
        $endDate = DateTimeImmutable::createFromFormat('U', '0');
        yield 'old-dates' => [
            new Timeframe(
                $startDate,
                $endDate,
                null,
                null
            ),
            [
                'start_date' => '1969-12-31',
                'end_date' => '1970-01-01',
            ],
        ];
    }

    public function failureGetData(): Generator
    {
        yield 'start-date-greater-than-end-date' => [
            new DateTimeImmutable('2020-01-01'),
            DateTimeImmutable::createFromFormat('U', '0'),
            null,
            null,
            'Start date [2020-01-01] should be lower than or equal to end date [1970-01-01].',
        ];
        yield 'invalid-source' => [
            new DateTimeImmutable('2020-01-01'),
            new DateTimeImmutable('2020-01-01'),
            'WRONG',
            null,
            '$source currency [WRONG] is not available.',
        ];
        yield 'invalid-currencies-1' => [
            new DateTimeImmutable('2020-01-01'),
            new DateTimeImmutable('2020-01-01'),
            null,
            ['WRONG'],
            'Currencies list contains not available values [WRONG].',
        ];
        yield 'invalid-currencies-2' => [
            new DateTimeImmutable('2020-01-01'),
            new DateTimeImmutable('2020-01-01'),
            null,
            [Currency::UAH, 'WRONG'],
            'Currencies list contains not available values [WRONG].',
        ];
    }
}
