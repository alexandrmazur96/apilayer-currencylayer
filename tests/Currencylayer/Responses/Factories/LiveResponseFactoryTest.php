<?php

namespace Apilayer\Tests\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Factories\LiveResponseFactory;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LiveResponseFactoryTest extends TestCase
{

    /**
     * @throws CurrencylayerException
     */
    public function testCreate(): void
    {
        $liveResponseFactory = new LiveResponseFactory();
        $liveResponse = $liveResponseFactory->create(
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
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
            'source' => Currency::UAH,
            'quotes' => [
                Currency::USD . Currency::UAH => 28.01,
                Currency::ALL . Currency::UZS => 1.21,
            ],
        ];

        self::assertEquals($expectedArrData, $liveResponse->toArray());
    }

    /**
     * @throws CurrencylayerException
     * @psalm-suppress InvalidArgument
     */
    public function testCreateWrongResponseFormat(): void
    {
        $liveResponseFactory = new LiveResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('Unexpected response from currencylayer API - []');
        $liveResponseFactory->create([]);
    }

    /**
     * @throws CurrencylayerException
     */
    public function testCreateFailedResponse(): void
    {
        $liveResponseFactory = new LiveResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('test error');
        $this->expectExceptionCode(777);
        $liveResponseFactory->create(
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
