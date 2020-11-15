<?php

namespace Apilayer\Currencylayer\Responses\Factories;

use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Responses\Timeframe;
use Apilayer\Currencylayer\Responses\DataAbstractResponse;
use Apilayer\Currencylayer\Exceptions\ApiFailedResponseException;
use Exception;

/**
 * @psalm-import-type _Timeframe from \Apilayer\Currencylayer\CurrencylayerClient
 * @psalm-import-type _ApiFailed from \Apilayer\Currencylayer\CurrencylayerClient
 * @template-implements ResponseFactoryInterface<_Timeframe>
 */
class TimeframeResponseFactory implements ResponseFactoryInterface
{
    /** @use ResponseFactoryTrait<_Timeframe|_ApiFailed> */
    use ResponseFactoryTrait;

    /**
     * @return Timeframe
     * @throws CurrencylayerException
     * @throws Exception
     *
     * @psalm-param _Timeframe|_ApiFailed $rawResponse
     */
    public function create(array $rawResponse): DataAbstractResponse
    {
        try {
            $this->validate($rawResponse);
        } catch (ApiFailedResponseException $e) {
            throw new CurrencylayerException($e->getMessage(), $e->getCode(), $e);
        }

        /** @psalm-var _Timeframe $rawResponse */

        return new Timeframe(
            $rawResponse['success'],
            $rawResponse['terms'],
            $rawResponse['privacy'],
            (bool)$rawResponse['timeframe'],
            $rawResponse['start_date'],
            $rawResponse['end_date'],
            $rawResponse['source'],
            $rawResponse['quotes']
        );
    }
}
