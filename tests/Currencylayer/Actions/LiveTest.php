<?php

namespace Apilayer\Tests\Currencylayer\Actions;

use Apilayer\Currencylayer\Actions\ActionInterface;
use Apilayer\Currencylayer\Actions\Live;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;
use Generator;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-import-type _LiveActionData from \Apilayer\Currencylayer\Actions\Live
 */
class LiveTest extends TestCase
{
    public function testGetEndpoint(): void
    {
        $liveAction = new Live(Currency::UAH, [Currency::USD, Currency::XOF]);
        self::assertEquals(ActionInterface::ENDPOINT_LIVE, $liveAction->getEndpoint());
    }

    /**
     * @param Live $liveAction
     * @dataProvider successGetData
     *
     * @psalm-param _LiveActionData $expectedResult
     */
    public function testGetDataSuccess(Live $liveAction, array $expectedResult): void
    {
        $actualResult = $liveAction->getData();
        self::assertEquals($expectedResult, $actualResult);
    }

    /**
     * @param string $expectedMessage
     * @dataProvider failureGetData
     *
     * @psalm-param Currency::*|null $source
     * @psalm-param list<Currency::*>|null $currencies
     */
    public function testGetDataFailure(?string $source, ?array $currencies, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        new Live($source, $currencies);
    }

    public function successGetData(): Generator
    {
        yield 'default-action' => [
            new Live(
                Currency::UAH,
                [Currency::UAH, Currency::USD]
            ),
            [
                'source' => Currency::UAH,
                'currencies' => [Currency::UAH, Currency::USD],
            ],
        ];

        yield 'without-optional' => [
            new Live(null, null),
            [],
        ];

        yield 'partial-optional-1' => [
            new Live(Currency::UAH, null),
            ['source' => Currency::UAH],
        ];

        yield 'partial-optional-2' => [
            new Live(null, [Currency::UAH, Currency::USD]),
            ['currencies' => [Currency::UAH, Currency::USD]],
        ];

        yield 'empty-currencies-list' => [
            new Live(null, []),
            [],
        ];
    }

    public function failureGetData(): Generator
    {
        yield 'wrong-source' => [
            'WRONG',
            null,
            '$source currency [WRONG] is not available.',
        ];

        yield 'wrong-currencies-1' => [
            null,
            ['WRONG'],
            'Currencies list contains not available values [WRONG].',
        ];

        yield 'wrong-currencies-2' => [
            null,
            ['WRONG', Currency::UAH],
            'Currencies list contains not available values [WRONG].',
        ];
    }
}
