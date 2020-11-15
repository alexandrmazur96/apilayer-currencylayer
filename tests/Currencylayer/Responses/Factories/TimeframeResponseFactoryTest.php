<?php

namespace Apilayer\Tests\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Factories\TimeframeResponseFactory;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TimeframeResponseFactoryTest extends TestCase
{
    /**
     * @throws CurrencylayerException
     */
    public function testCreate(): void
    {
        $timeframeResponseFactory = new TimeframeResponseFactory();
        $timeframeResponse = $timeframeResponseFactory->create(
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'timeframe' => true,
                'start_date' => '2020-01-01',
                'end_date' => '2020-01-02',
                'source' => Currency::UAH,
                'quotes' => [
                    '2020-01-01' => [
                        Currency::USD . Currency::UAH => 28.01,
                        Currency::ALL . Currency::UZS => 1.21,
                    ],
                    '2020-01-02' => [
                        Currency::USD . Currency::UAH => 28.01,
                        Currency::ALL . Currency::UZS => 1.21,
                    ],
                ],
            ]
        );

        $expectedArrData = [
            'success' => true,
            'terms' => 'test_terms',
            'privacy' => 'test_privacy',
            'timeframe' => true,
            'start_date' => '2020-01-01',
            'end_date' => '2020-01-02',
            'source' => Currency::UAH,
            'quotes' => [
                '2020-01-01' => [
                    Currency::USD . Currency::UAH => 28.01,
                    Currency::ALL . Currency::UZS => 1.21,
                ],
                '2020-01-02' => [
                    Currency::USD . Currency::UAH => 28.01,
                    Currency::ALL . Currency::UZS => 1.21,
                ],
            ],
        ];

        self::assertEquals($expectedArrData, $timeframeResponse->toArray());
    }

    /**
     * @throws CurrencylayerException
     * @psalm-suppress InvalidArgument
     */
    public function testCreateWrongResponseFormat(): void
    {
        $timeframeResponseFactory = new TimeframeResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('Unexpected response from currencylayer API - []');
        $timeframeResponseFactory->create([]);
    }

    /**
     * @throws CurrencylayerException
     */
    public function testCreateFailedResponse(): void
    {
        $timeframeResponseFactory = new TimeframeResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('test error');
        $this->expectExceptionCode(777);
        $timeframeResponseFactory->create(
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
