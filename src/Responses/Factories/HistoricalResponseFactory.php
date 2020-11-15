<?php

namespace Apilayer\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Historical;
use Apilayer\Currencylayer\Responses\DataAbstractResponse;
use Apilayer\Currencylayer\Exceptions\ApiFailedResponseException;
use Exception;

/**
 * @psalm-import-type _Historical from \Apilayer\Currencylayer\CurrencylayerClient
 * @psalm-import-type _ApiFailed from \Apilayer\Currencylayer\CurrencylayerClient
 * @template-implements ResponseFactoryInterface<_Historical>
 */
class HistoricalResponseFactory implements ResponseFactoryInterface
{
    /** @use ResponseFactoryTrait<_Historical|_ApiFailed> */
    use ResponseFactoryTrait;

    /**
     * @return Historical
     * @throws CurrencylayerException
     * @throws Exception
     *
     * @psalm-param _Historical|_ApiFailed $rawResponse
     */
    public function create(array $rawResponse): DataAbstractResponse
    {
        try {
            $this->validate($rawResponse);
        } catch (ApiFailedResponseException $e) {
            throw new CurrencylayerException($e->getMessage(), $e->getCode(), $e);
        }

        /** @psalm-var _Historical $rawResponse */

        return new Historical(
            $rawResponse['success'],
            $rawResponse['terms'],
            $rawResponse['privacy'],
            (bool)$rawResponse['historical'],
            $rawResponse['date'],
            $rawResponse['timestamp'],
            $rawResponse['source'],
            $rawResponse['quotes']
        );
    }
}
