<?php

namespace Apilayer\Currencylayer;

use Apilayer\Currencylayer\Actions\ActionInterface;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\DataAbstractResponse;
use Apilayer\Currencylayer\Enums\HttpSchema;
use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-type _ApiFailed=array{
 *      success:false,
 *      error:array{
 *          code:int,
 *          info:string
 *      }
 * }
 *
 * @psalm-type _Change=array{
 *      success:true,
 *      terms:string,
 *      privacy:string,
 *      change:bool,
 *      start_date:string,
 *      end_date:string,
 *      source:Currency::*,
 *      quotes:array<string,array{
 *          start_rate:float,
 *          end_rate:float,
 *          change:float,
 *          change_pct:float
 *      }>
 * }
 *
 * @psalm-type _Convert=array{
 *      success:true,
 *      terms:string,
 *      privacy:string,
 *      query:array{
 *          from:Currency::*,
 *          to:Currency::*,
 *          amount:float
 *      },
 *      info:array{
 *          timestamp:int,
 *          quote:float
 *      },
 *      result:float,
 *      historical?:bool,
 *      date?:string
 * }
 *
 * @psalm-type _Historical=array{
 *      success:true,
 *      terms:string,
 *      privacy:string,
 *      historical:bool,
 *      date:string,
 *      timestamp:int,
 *      source:Currency::*,
 *      quotes:array<string,float>
 * }
 *
 * @psalm-type _List=array{
 *      success:true,
 *      terms:string,
 *      privacy:string,
 *      currencies:list<Currency::*>
 * }
 *
 * @psalm-type _Live=array{
 *      success:true,
 *      terms:string,
 *      privacy:string,
 *      timestamp:int,
 *      source:Currency::*,
 *      quotes:array<string,float>
 * }
 *
 * @psalm-type _Timeframe=array{
 *      success:true,
 *      terms:string,
 *      privacy:string,
 *      timeframe:bool,
 *      start_date:string,
 *      end_date:string,
 *      source:Currency::*,
 *      quotes:array<string,array<string,float>>
 * }
 */
class CurrencylayerClient extends BaseClient
{
    /**
     * @param ClientInterface $httpClient
     * @param RequestFactoryInterface $httpRequestFactory
     * @param string $apiKey
     * @param string $schema
     * @throws InvalidArgumentException
     */
    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $httpRequestFactory,
        string $apiKey,
        string $schema = HttpSchema::SCHEMA_HTTP
    ) {
        $this->apiKey = $apiKey;
        $this->httpRequestFactory = $httpRequestFactory;
        $this->httpClient = $httpClient;
        if (!in_array($schema, [HttpSchema::SCHEMA_HTTP, HttpSchema::SCHEMA_HTTPS], true)) {
            throw new InvalidArgumentException('Invalid schema passed!');
        }
        $this->schema = $schema;
    }

    /**
     * @param ActionInterface $action
     * @return DataAbstractResponse
     * @throws CurrencylayerException
     */
    public function perform(ActionInterface $action): DataAbstractResponse
    {
        $apiUrl = $this->buildApiUrl($action->getEndpoint(), $this->prepareData($action));

        $request = $this->httpRequestFactory->createRequest(
            'GET',
            $apiUrl
        );

        try {
            $response = $this->httpClient->sendRequest($request);
            /** @psalm-var _ApiFailed|_Change|_Convert|_Historical|_List|_Live|_Timeframe $rawResponse */
            $rawResponse = json_decode(
                $response->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException | ClientExceptionInterface $e) {
            $code = (int)$e->getCode();
            throw new CurrencylayerException($e->getMessage(), $code, $e->getPrevious());
        }

        $responseFactory = $action->getResponseFactory();

        return $responseFactory->create($rawResponse);
    }

    /** @psalm-return 'api.currencylayer.com' */
    public function getApiBaseUrl(): string
    {
        return 'api.currencylayer.com';
    }
}
