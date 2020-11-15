<?php

namespace Apilayer\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Change;
use Apilayer\Currencylayer\Responses\DataAbstractResponse;
use Apilayer\Currencylayer\Exceptions\ApiFailedResponseException;
use Exception;

/**
 * @psalm-import-type _Change from \Apilayer\Currencylayer\CurrencylayerClient
 * @psalm-import-type _ApiFailed from \Apilayer\Currencylayer\CurrencylayerClient
 * @template-implements ResponseFactoryInterface<_Change>
 */
class ChangeResponseFactory implements ResponseFactoryInterface
{
    /** @use ResponseFactoryTrait<_Change|_ApiFailed> */
    use ResponseFactoryTrait;

    /**
     * @return Change
     * @throws CurrencylayerException
     * @throws Exception
     *
     * @psalm-param _Change|_ApiFailed $rawResponse
     */
    public function create(array $rawResponse): DataAbstractResponse
    {
        try {
            $this->validate($rawResponse);
        } catch (ApiFailedResponseException $e) {
            throw new CurrencylayerException($e->getMessage(), $e->getCode(), $e);
        }

        /** @psalm-var _Change $rawResponse */

        return new Change(
            $rawResponse['success'],
            $rawResponse['terms'],
            $rawResponse['privacy'],
            $rawResponse['change'],
            $rawResponse['start_date'],
            $rawResponse['end_date'],
            $rawResponse['source'],
            $rawResponse['quotes']
        );
    }
}
