<?php

namespace Apilayer\Tests\Currencylayer\Responses;

use Apilayer\Currencylayer\Responses\Timeframe;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;
use DateTimeImmutable;
use Exception;
use JsonException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TimeframeTest extends TestCase
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
        $timeframe = true;
        $startDate = new DateTimeImmutable('2020-01-01');
        $endDate = new DateTimeImmutable('2020-01-02');
        $quotes = [
            '2020-01-01' => [
                Currency::USD . Currency::UAH => 28.01,
                Currency::ALL . Currency::UZS => 1.21,
            ],
            '2020-01-02' => [
                Currency::USD . Currency::UAH => 28.01,
                Currency::ALL . Currency::UZS => 1.21,
            ],
        ];

        $responseObj = new Timeframe(
            $success,
            $terms,
            $privacy,
            $timeframe,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            Currency::UAH,
            $quotes
        );

        $expectedArrData = [
            'success' => $success,
            'terms' => $terms,
            'privacy' => $privacy,
            'timeframe' => $timeframe,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'source' => Currency::UAH,
            'quotes' => [
                '2020-01-01' => [
                    'USDUAH' => 28.01,
                    'ALLUZS' => 1.21,
                ],
                '2020-01-02' => [
                    'USDUAH' => 28.01,
                    'ALLUZS' => 1.21,
                ],
            ],
        ];

        $this->assertCommonResponseParameters(
            $success,
            $terms,
            $privacy,
            $responseObj
        );
        self::assertEquals($startDate, $responseObj->getStartDate());
        self::assertEquals($endDate, $responseObj->getEndDate());
        self::assertEquals(Currency::UAH, $responseObj->getSource());
        self::assertEquals(
            [
                '2020-01-01' => [
                    'USDUAH' => 28.01,
                    'ALLUZS' => 1.21,
                ],
                '2020-01-02' => [
                    'USDUAH' => 28.01,
                    'ALLUZS' => 1.21,
                ],
            ],
            $responseObj->getQuotes()
        );
        self::assertEquals($expectedArrData, $responseObj->toArray());
        $responseObjJson = json_encode($responseObj, JSON_THROW_ON_ERROR);
        self::assertJson($responseObjJson);
        self::assertEquals(json_encode($expectedArrData, JSON_THROW_ON_ERROR), $responseObjJson);
    }
}
