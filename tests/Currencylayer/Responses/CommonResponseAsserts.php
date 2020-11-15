<?php

namespace Apilayer\Tests\Currencylayer\Responses;

use Apilayer\Currencylayer\Responses\DataAbstractResponse;

trait CommonResponseAsserts
{
    private function assertCommonResponseParameters(
        bool $expectedSuccess,
        string $expectedTerms,
        string $expectedPrivacy,
        DataAbstractResponse $response
    ): void {
        self::assertEquals($expectedSuccess, $response->isSuccess());
        self::assertEquals($expectedTerms, $response->getTerms());
        self::assertEquals($expectedPrivacy, $response->getPrivacy());
    }
}
