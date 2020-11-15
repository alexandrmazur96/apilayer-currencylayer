<?php

namespace Apilayer\Tests\Currencylayer\Responses;

use Apilayer\Currencylayer\Responses\Historical;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;
use DateTimeImmutable;
use Exception;
use JsonException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class HistoricalTest extends TestCase
{
    use CommonResponseAsserts;

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function testResponseData(): void
    {
        $success = true;
        $terms = 'test_terms';
        $privacy = 'test_privacy';
        $historical = true;
        $date = new DateTimeImmutable('2020-01-01');
        $quotes = [
            Currency::USD . Currency::UAH => 28.01,
            Currency::ALL . Currency::UZS => 1.21,
        ];

        $responseObj = new Historical(
            $success,
            $terms,
            $privacy,
            $historical,
            $date->format('Y-m-d'),
            $date->getTimestamp(),
            Currency::UAH,
            $quotes
        );

        $expectedArrData = [
            'success' => true,
            'terms' => 'test_terms',
            'privacy' => 'test_privacy',
            'timestamp' => $date->getTimestamp(),
            'historical' => $historical,
            'date' => $date->format('Y-m-d'),
            'source' => Currency::UAH,
            'quotes' => ['USDUAH' => 28.01, 'ALLUZS' => 1.21],
        ];

        $this->assertCommonResponseParameters(
            $success,
            $terms,
            $privacy,
            $responseObj
        );
        self::assertEquals($date, $responseObj->getTimestamp());
        self::assertEquals(Currency::UAH, $responseObj->getSource());
        self::assertEquals(['USDUAH' => 28.01, 'ALLUZS' => 1.21], $responseObj->getQuotes());
        self::assertEquals($expectedArrData, $responseObj->toArray());
        $responseObjJson = json_encode($responseObj, JSON_THROW_ON_ERROR);
        self::assertJson($responseObjJson);
        self::assertEquals(json_encode($expectedArrData, JSON_THROW_ON_ERROR), $responseObjJson);
    }
}
