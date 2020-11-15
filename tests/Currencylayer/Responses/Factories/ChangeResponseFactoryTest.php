<?php

namespace Apilayer\Tests\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Factories\ChangeResponseFactory;
use Apilayer\Currencylayer\ValueObjects\ChangeInfo;
use Apilayer\Currencylayer\ValueObjects\ChangeQuote;
use Apilayer\Tests\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ChangeResponseFactoryTest extends TestCase
{
    /**
     * @throws CurrencylayerException
     */
    public function testCreate(): void
    {
        $changeResponseFactory = new ChangeResponseFactory();
        $changeResponse = $changeResponseFactory->create(
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'change' => true,
                'start_date' => '2020-01-01',
                'end_date' => '2020-01-02',
                'source' => Currency::UAH,
                'quotes' => [
                    'USDUAH' => [
                        'start_rate' => 1.28123,
                        'end_rate' => 1.108609,
                        'change' => -0.172621,
                        'change_pct' => -13.4735,
                    ],
                    'USDEUR' => [
                        'start_rate' => 1.48123,
                        'end_rate' => 1.508609,
                        'change' => 0.027379,
                        'change_pct' => 2.379,
                    ],
                ],
            ]
        );

        $expectedQuotes1 = new ChangeQuote(
            Currency::USD . Currency::UAH,
            new ChangeInfo(
                1.28123,
                1.108609,
                -0.172621,
                -13.4735
            )
        );
        $expectedQuotes2 = new ChangeQuote(
            Currency::USD . Currency::EUR,
            new ChangeInfo(
                1.48123,
                1.508609,
                0.027379,
                2.379
            )
        );
        $expectedArrData = [
            'success' => true,
            'terms' => 'test_terms',
            'privacy' => 'test_privacy',
            'change' => true,
            'start_date' => '2020-01-01',
            'end_date' => '2020-01-02',
            'source' => Currency::UAH,
            'quotes' => [$expectedQuotes1, $expectedQuotes2],
        ];

        self::assertEquals($expectedArrData, $changeResponse->toArray());
    }

    /**
     * @throws CurrencylayerException
     * @psalm-suppress InvalidArgument
     */
    public function testCreateWrongResponseFormat(): void
    {
        $changeResponseFactory = new ChangeResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('Unexpected response from currencylayer API - []');
        $changeResponseFactory->create([]);
    }

    /**
     * @throws CurrencylayerException
     */
    public function testCreateFailedResponse(): void
    {
        $changeResponseFactory = new ChangeResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('test error');
        $this->expectExceptionCode(777);
        $changeResponseFactory->create(
            [
                'success' => false,
                'error' => [
                    'info' => 'test error',
                    'code' => 777,
                ],
            ]
        );
    }
}
