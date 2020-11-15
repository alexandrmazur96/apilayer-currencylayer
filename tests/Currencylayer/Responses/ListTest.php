<?php

namespace Apilayer\Tests\Currencylayer\Responses;

use Apilayer\Currencylayer\Responses\ListResponse;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Tests\TestCase;
use JsonException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ListTest extends TestCase
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
        $currencies = Currency::getAvailableCurrencies();

        $list = new ListResponse($success, $terms, $privacy, $currencies);
        $this->assertCommonResponseParameters(
            $success,
            $terms,
            $privacy,
            $list
        );
        $expectedArrData = [
            'success' => true,
            'terms' => 'test_terms',
            'privacy' => 'test_privacy',
            'currencies' => Currency::getAvailableCurrencies(),
        ];
        self::assertEquals($expectedArrData, $list->toArray());
        $listJson = json_encode($list, JSON_THROW_ON_ERROR);
        self::assertJson($listJson);
        self::assertEquals(json_encode($expectedArrData, JSON_THROW_ON_ERROR), $listJson);
    }
}
