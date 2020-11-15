<?php

namespace Apilayer\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Responses\DataAbstractResponse;

/**
 * @psalm-template T
 */
interface ResponseFactoryInterface
{
    /**
     * @psalm-param T $rawResponse
     * @return DataAbstractResponse
     */
    public function create(array $rawResponse): DataAbstractResponse;
}
