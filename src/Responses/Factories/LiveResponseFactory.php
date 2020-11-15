<?php

namespace Apilayer\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Live;
use Apilayer\Currencylayer\Responses\DataAbstractResponse;
use Apilayer\Currencylayer\Exceptions\ApiFailedResponseException;

/**
 * @psalm-import-type _Live from \Apilayer\Currencylayer\CurrencylayerClient
 * @psalm-import-type _ApiFailed from \Apilayer\Currencylayer\CurrencylayerClient
 * @template-implements ResponseFactoryInterface<_Live>
 */
class LiveResponseFactory implements ResponseFactoryInterface
{
    /** @use ResponseFactoryTrait<_Live|_ApiFailed> */
    use ResponseFactoryTrait;

    /**
     * @return Live
     * @throws CurrencylayerException
     *
     * @psalm-param _Live|_ApiFailed $rawResponse
     */
    public function create(array $rawResponse): DataAbstractResponse
    {
        try {
            $this->validate($rawResponse);
        } catch (ApiFailedResponseException $e) {
            throw new CurrencylayerException($e->getMessage(), $e->getCode(), $e);
        }

        /** @psalm-var _Live $rawResponse */

        return new Live(
            $rawResponse['success'],
            $rawResponse['terms'],
            $rawResponse['privacy'],
            (int)$rawResponse['timestamp'],
            $rawResponse['source'],
            $rawResponse['quotes']
        );
    }
}
