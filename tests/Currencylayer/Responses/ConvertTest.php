<?php

namespace Apilayer\Tests\Currencylayer\Responses;

use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Currencylayer\Responses\Convert;
use Apilayer\Currencylayer\ValueObjects\CurrencylayerInfo;
use Apilayer\Currencylayer\ValueObjects\Query;
use Apilayer\Tests\TestCase;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Generator;
use JsonException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-import-type _Query from \Apilayer\Currencylayer\Responses\Convert
 * @psalm-import-type _Info from \Apilayer\Currencylayer\Responses\Convert
 */
class ConvertTest extends TestCase
{
    use CommonResponseAsserts;

    /**
     * @dataProvider responseData
     * @param bool $success
     * @param string $terms
     * @param string $privacy
     * @param float $result
     * @param bool|null $historical
     * @param DateTimeInterface|null $date
     * @throws JsonException
     * @throws Exception
     *
     * @psalm-param _Query $query
     * @psalm-param _Info $info
     */
    public function testResponseData(
        bool $success,
        string $terms,
        string $privacy,
        array $query,
        array $info,
        float $result,
        ?bool $historical,
        ?DateTimeInterface $date
    ): void {
        $responseObj = new Convert(
            $success,
            $terms,
            $privacy,
            $query,
            $info,
            $result,
            $historical,
            $date === null ? null : $date->format('Y-m-d'),
        );

        $this->assertCommonResponseParameters(
            $success,
            $terms,
            $privacy,
            $responseObj
        );

        $expectedResult = [
            'success' => $success,
            'terms' => $terms,
            'privacy' => $privacy,
            'query' => new Query($query['from'], $query['to'], $query['amount']),
            'info' => new CurrencylayerInfo($info['timestamp'], $info['quote']),
            'result' => $result,
        ];

        if ($historical !== null) {
            $expectedResult['historical'] = $historical;
        }

        if ($date !== null) {
            $expectedResult['date'] = $date->format('Y-m-d');
        }

        self::assertEquals($result, $responseObj->getResult());
        self::assertEquals($expectedResult, $responseObj->toArray());
        $responseObjJson = json_encode($responseObj, JSON_THROW_ON_ERROR);
        self::assertJson($responseObjJson);
        self::assertEquals(json_encode($expectedResult, JSON_THROW_ON_ERROR), $responseObjJson);
    }

    public function responseData(): Generator
    {
        yield 'empty-optional' => [
            true,
            'test_terms',
            'test_privacy',
            [
                'from' => Currency::USD,
                'to' => Currency::UAH,
                'amount' => 10,
            ],
            [
                'timestamp' => (new DateTimeImmutable('2020-01-01'))->getTimestamp(),
                'quote' => 6.58443,
            ],
            6.58443,
            null,
            null,
        ];

        yield 'partial-optional-1' => [
            true,
            'test_terms',
            'test_privacy',
            [
                'from' => Currency::USD,
                'to' => Currency::UAH,
                'amount' => 10,
            ],
            [
                'timestamp' => (new DateTimeImmutable('2020-01-01'))->getTimestamp(),
                'quote' => 6.58443,
            ],
            6.58443,
            true,
            null,
        ];

        yield 'partial-optional-2' => [
            true,
            'test_terms',
            'test_privacy',
            [
                'from' => Currency::USD,
                'to' => Currency::UAH,
                'amount' => 10,
            ],
            [
                'timestamp' => (new DateTimeImmutable('2020-01-01'))->getTimestamp(),
                'quote' => 6.58443,
            ],
            6.58443,
            null,
            new DateTimeImmutable('2020-01-05'),
        ];

        yield 'filled-optional' => [
            true,
            'test_terms',
            'test_privacy',
            [
                'from' => Currency::USD,
                'to' => Currency::UAH,
                'amount' => 10,
            ],
            [
                'timestamp' => (new DateTimeImmutable('2020-01-01'))->getTimestamp(),
                'quote' => 6.58443,
            ],
            6.58443,
            true,
            new DateTimeImmutable('2020-01-05'),
        ];
    }
}
