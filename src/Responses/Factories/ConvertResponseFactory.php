<?php

namespace Apilayer\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Convert;
use Apilayer\Currencylayer\Responses\DataAbstractResponse;
use Apilayer\Currencylayer\Exceptions\ApiFailedResponseException;
use Exception;

/**
 * @psalm-import-type _Convert from \Apilayer\Currencylayer\CurrencylayerClient
 * @psalm-import-type _ApiFailed from \Apilayer\Currencylayer\CurrencylayerClient
 * @template-implements ResponseFactoryInterface<_Convert>
 */
class ConvertResponseFactory implements ResponseFactoryInterface
{
    /** @use ResponseFactoryTrait<_Convert|_ApiFailed> */
    use ResponseFactoryTrait;

    /**
     * @return Convert
     * @throws CurrencylayerException
     * @throws Exception
     *
     * @psalm-param _Convert|_ApiFailed $rawResponse
     */
    public function create(array $rawResponse): DataAbstractResponse
    {
        try {
            $this->validate($rawResponse);
        } catch (ApiFailedResponseException $e) {
            throw new CurrencylayerException($e->getMessage(), $e->getCode(), $e);
        }

        /** @psalm-var _Convert $rawResponse */

        return new Convert(
            $rawResponse['success'],
            $rawResponse['terms'],
            $rawResponse['privacy'],
            $rawResponse['query'],
            $rawResponse['info'],
            $rawResponse['result'],
            $rawResponse['historical'] ?? null,
            $rawResponse['date'] ?? null
        );
    }
}
