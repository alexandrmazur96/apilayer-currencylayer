<?php

namespace Apilayer\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\ListResponse;
use Apilayer\Currencylayer\Responses\DataAbstractResponse;
use Apilayer\Currencylayer\Exceptions\ApiFailedResponseException;

/**
 * @psalm-import-type _List from \Apilayer\Currencylayer\CurrencylayerClient
 * @psalm-import-type _ApiFailed from \Apilayer\Currencylayer\CurrencylayerClient
 * @template-implements ResponseFactoryInterface<_List>
 */
class ListResponseFactory implements ResponseFactoryInterface
{
    /** @use ResponseFactoryTrait<_List|_ApiFailed> */
    use ResponseFactoryTrait;

    /**
     * @return ListResponse
     * @throws CurrencylayerException
     *
     * @psalm-param _List|_ApiFailed $rawResponse
     */
    public function create(array $rawResponse): DataAbstractResponse
    {
        try {
            $this->validate($rawResponse);
        } catch (ApiFailedResponseException $e) {
            throw new CurrencylayerException($e->getMessage(), $e->getCode(), $e);
        }

        /** @psalm-var _List $rawResponse */

        return new ListResponse(
            $rawResponse['success'],
            $rawResponse['terms'],
            $rawResponse['privacy'],
            $rawResponse['currencies']
        );
    }
}
