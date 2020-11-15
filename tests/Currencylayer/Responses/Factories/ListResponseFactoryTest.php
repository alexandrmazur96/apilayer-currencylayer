<?php

namespace Apilayer\Tests\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Factories\ListResponseFactory;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ListResponseFactoryTest extends TestCase
{
    /**
     * @throws CurrencylayerException
     */
    public function testCreate(): void
    {
        $listResponseFactory = new ListResponseFactory();
        $listResponse = $listResponseFactory->create(
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'currencies' => Currency::getAvailableCurrencies(),
            ]
        );

        self::assertEquals(
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'currencies' => Currency::getAvailableCurrencies(),
            ],
            $listResponse->toArray()
        );
    }

    /**
     * @throws CurrencylayerException
     * @psalm-suppress InvalidArgument
     */
    public function testCreateWrongResponseFormat(): void
    {
        $listResponseFactory = new ListResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('Unexpected response from currencylayer API - []');
        $listResponseFactory->create([]);
    }

    /**
     * @throws CurrencylayerException
     */
    public function testCreateFailedResponse(): void
    {
        $listResponseFactory = new ListResponseFactory();

        $this->expectException(CurrencylayerException::class);
        $this->expectExceptionMessage('test error');
        $this->expectExceptionCode(777);
        $listResponseFactory->create(
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
