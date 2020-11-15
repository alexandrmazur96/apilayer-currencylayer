<?php

namespace Apilayer\Tests\Currencylayer\Responses;

use Apilayer\Currencylayer\Responses\Change;
use Apilayer\Currencylayer\ValueObjects\ChangeInfo;
use Apilayer\Currencylayer\ValueObjects\ChangeQuote;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;
use DateTimeImmutable;
use Exception;
use JsonException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ChangeTest extends TestCase
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
        $change = true;
        $startDate = new DateTimeImmutable('2020-01-01');
        $endDate = new DateTimeImmutable('2020-01-05');
        $quotes = [
            Currency::USD . Currency::UAH => [
                'start_rate' => 1.28123,
                'end_rate' => 1.108609,
                'change' => -0.172621,
                'change_pct' => -13.4735,
            ],
            Currency::USD . Currency::EUR => [
                'start_rate' => 1.48123,
                'end_rate' => 1.508609,
                'change' => 0.027379,
                'change_pct' => 2.379,
            ],
        ];

        $responseObj = new Change(
            $success,
            $terms,
            $privacy,
            $change,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            Currency::UAH,
            $quotes
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
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'source' => Currency::UAH,
            'quotes' => [$expectedQuotes1, $expectedQuotes2],
        ];

        $this->assertCommonResponseParameters(
            $success,
            $terms,
            $privacy,
            $responseObj
        );
        self::assertEquals(Currency::UAH, $responseObj->getSource());
        self::assertEquals([$expectedQuotes1, $expectedQuotes2], $responseObj->getQuotes());
        self::assertEquals($expectedArrData, $responseObj->toArray());
        $responseObjJson = json_encode($responseObj, JSON_THROW_ON_ERROR);
        self::assertJson($responseObjJson);
        self::assertEquals(json_encode($expectedArrData, JSON_THROW_ON_ERROR), $responseObjJson);
    }
}
