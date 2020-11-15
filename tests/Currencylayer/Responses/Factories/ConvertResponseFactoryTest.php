<?php

namespace Apilayer\Tests\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Factories\ConvertResponseFactory;
use Apilayer\Currencylayer\ValueObjects\CurrencylayerInfo;
use Apilayer\Currencylayer\ValueObjects\Query;
use Apilayer\Tests\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ConvertResponseFactoryTest extends TestCase
{
    /**
     * @throws CurrencylayerException
     */
    public function testCreate(): void
    {
        $convertResponseFactory = new ConvertResponseFactory();
        $convertResponse = $convertResponseFactory->create(
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'query' => [
                    'from' => Currency::USD,
                    'to' => Currency::UAH,
                    'amount' => 10,
                ],
                'info' => [
                    'timestamp' => 1601983403,
                    'quote' => 6.58443,
                ],
                'result' => 1.123456,
                'historical' => true,
                'date' => '2020-01-01',
            ]
        );

        $expectedResult = [
            'success' => true,
            'terms' => 'test_terms',
            'privacy' => 'test_privacy',
            'query' => new Query(Currency::USD, Currency::UAH, 10),
            'info' => new CurrencylayerInfo(1601983403, 6.58443),
            'result' => 1.123456,
            'historical' => true,
            'date' => '2020-01-01',
        ];

        self::assertEquals($expectedResult, $convertResponse->toArray());
    }

    /**
     * @throws CurrencylayerException
     * @psalm-suppress InvalidArgument
     */
    public function testCreateWrongResponseFormat(): void
    {
        $convertResponseFactory = new ConvertResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('Unexpected response from currencylayer API - []');
        $convertResponseFactory->create([]);
    }

    /**
     * @throws CurrencylayerException
     */
    public function testCreateFailedResponse(): void
    {
        $convertResponseFactory = new ConvertResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('test error');
        $this->expectExceptionCode(777);
        $convertResponseFactory->create(
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
