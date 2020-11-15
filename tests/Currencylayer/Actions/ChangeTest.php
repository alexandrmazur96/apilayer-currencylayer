<?php

namespace Apilayer\Tests\Currencylayer\Actions;

use Apilayer\Currencylayer\Actions\ActionInterface;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Currencylayer\Actions\Change as ChangeAction;
use Apilayer\Tests\TestCase;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;

/**
 * @psalm-import-type _ChangeActionData from \Apilayer\Currencylayer\Actions\Change
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ChangeTest extends TestCase
{
    public function testGetEndpoint(): void
    {
        $changeAction = new ChangeAction(
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            Currency::USD,
            [Currency::UAH]
        );
        self::assertEquals(ActionInterface::ENDPOINT_CHANGE, $changeAction->getEndpoint());
    }

    /**
     * @param ChangeAction $changeAction
     * @dataProvider successGetData
     *
     * @psalm-param _ChangeActionData $expectedResult
     */
    public function testGetDataSuccess(ChangeAction $changeAction, array $expectedResult): void
    {
        $actualResult = $changeAction->getData();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * @param DateTimeInterface $startDate
     * @param DateTimeInterface $endDate
     * @param string $expectedMessage
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
        new ChangeAction($startDate, $endDate, $source, $currencies);
    }

    public function successGetData(): Generator
    {
        yield 'default-action' => [
            new ChangeAction(
                new DateTimeImmutable('2020-01-01'),
                new DateTimeImmutable('2020-02-01'),
                Currency::UAH,
                [Currency::USD]
            ),
            [
                'start_date' => '2020-01-01',
                'end_date' => '2020-02-01',
                'source' => Currency::UAH,
                'currencies' => [Currency::USD],
            ],
        ];

        yield 'without-optional' => [
            new ChangeAction(
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

        yield 'with-partial-optional' => [
            new ChangeAction(
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

        /** @var DateTimeInterface $startDate */
        $startDate = DateTimeImmutable::createFromFormat('U', '-1');
        /** @var DateTimeInterface $endDate */
        $endDate = DateTimeImmutable::createFromFormat('U', '0');
        yield 'old-dates' => [
            new ChangeAction(
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

        /** @var DateTimeInterface $startDate */
        $startDate = DateTimeImmutable::createFromFormat('U', '0');
        /** @var DateTimeInterface $endDate */
        $endDate = DateTimeImmutable::createFromFormat('U', '0');
        yield 'equal-dates' => [
            new ChangeAction(
                $startDate,
                $endDate,
                null,
                null
            ),
            [
                'start_date' => '1970-01-01',
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
            '$source currency [WRONG] is not available.'
        ];
        yield 'invalid-currencies-1' => [
            new DateTimeImmutable('2020-01-01'),
            new DateTimeImmutable('2020-01-01'),
            null,
            ['WRONG'],
            'Currencies list contains not available values [WRONG].'
        ];
        yield 'invalid-currencies-2' => [
            new DateTimeImmutable('2020-01-01'),
            new DateTimeImmutable('2020-01-01'),
            null,
            [Currency::UAH, 'WRONG'],
            'Currencies list contains not available values [WRONG].'
        ];
    }
}
