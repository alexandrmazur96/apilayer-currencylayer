<?php

namespace Apilayer\Tests\Currencylayer\Responses;

use Apilayer\Currencylayer\Responses\Live;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;
use DateTimeImmutable;
use JsonException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LiveTest extends TestCase
{
    use CommonResponseAsserts;

    /**
     * @throws JsonException
     */
    public function testResponseData(): void
    {
        $success = true;
        $terms = 'test_terms';
        $privacy = 'test_privacy';
        $date = new DateTimeImmutable('2020-01-01');
        $quotes = [
            Currency::USD . Currency::UAH => 28.01,
            Currency::ALL . Currency::UZS => 1.21,
        ];
        $expectedArrData = [
            'success' => true,
            'terms' => 'test_terms',
            'privacy' => 'test_privacy',
            'timestamp' => $date->getTimestamp(),
            'source' => Currency::UAH,
            'quotes' => ['USDUAH' => 28.01, 'ALLUZS' => 1.21],
        ];

        $responseObj = new Live(
            $success,
            $terms,
            $privacy,
            $date->getTimestamp(),
            Currency::UAH,
            $quotes
        );

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
