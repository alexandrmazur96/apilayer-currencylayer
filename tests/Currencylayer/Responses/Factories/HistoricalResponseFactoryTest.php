<?php

namespace Apilayer\Tests\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Factories\HistoricalResponseFactory;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class HistoricalResponseFactoryTest extends TestCase
{
    /**
     * @throws CurrencylayerException
     */
    public function testCreate(): void
    {
        $historicalResponseFactory = new HistoricalResponseFactory();
        $historicalResponse = $historicalResponseFactory->create(
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'historical' => true,
                'date' => '2020-01-01',
                'timestamp' => 1601983403,
                'source' => Currency::UAH,
                'quotes' => [
                    Currency::USD . Currency::UAH => 28.01,
                    Currency::ALL . Currency::UZS => 1.21,
                ],
            ]
        );

        $expectedArrData = [
            'success' => true,
            'terms' => 'test_terms',
            'privacy' => 'test_privacy',
            'timestamp' => 1601983403,
            'historical' => true,
            'date' => '2020-01-01',
            'source' => Currency::UAH,
            'quotes' => [
                Currency::USD . Currency::UAH => 28.01,
                Currency::ALL . Currency::UZS => 1.21,
            ],
        ];

        self::assertEquals($expectedArrData, $historicalResponse->toArray());
    }

    /**
     * @throws CurrencylayerException
     * @psalm-suppress InvalidArgument
     */
    public function testCreateWrongResponseFormat(): void
    {
        $historicalResponseFactory = new HistoricalResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('Unexpected response from currencylayer API - []');
        $historicalResponseFactory->create([]);
    }

    /**
     * @throws CurrencylayerException
     */
    public function testCreateFailedResponse(): void
    {
        $historicalResponseFactory = new HistoricalResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('test error');
        $this->expectExceptionCode(777);
        $historicalResponseFactory->create(
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
