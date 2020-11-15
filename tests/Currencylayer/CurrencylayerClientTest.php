<?php

/** @noinspection PhpUndefinedMethodInspection */

namespace Apilayer\Tests\Currencylayer;

use Apilayer\Currencylayer\Actions\ActionInterface;
use Apilayer\Currencylayer\CurrencylayerClient;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Factories\ChangeResponseFactory;
use Apilayer\Currencylayer\Responses\Factories\ConvertResponseFactory;
use Apilayer\Currencylayer\Responses\Factories\HistoricalResponseFactory;
use Apilayer\Currencylayer\Responses\Factories\ListResponseFactory;
use Apilayer\Currencylayer\Responses\Factories\LiveResponseFactory;
use Apilayer\Currencylayer\Responses\Factories\TimeframeResponseFactory;
use Apilayer\Currencylayer\ValueObjects\ChangeInfo;
use Apilayer\Currencylayer\ValueObjects\ChangeQuote;
use Apilayer\Currencylayer\ValueObjects\CurrencylayerInfo;
use Apilayer\Currencylayer\ValueObjects\Query;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Currencylayer\Enums\HttpSchema;
use Apilayer\Tests\TestCase;
use Apilayer\Tests\Utils\PsrProphecyMocker;
use Exception;
use Generator;
use JsonException;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Apilayer\Currencylayer\Responses\Factories\ResponseFactoryInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CurrencylayerClientTest extends TestCase
{
    use ProphecyTrait;
    use PsrProphecyMocker;

    /** @psalm-var 'test_api_key' */
    private const TEST_API_KEY = 'test_api_key';

    /**
     * @throws InvalidArgumentException
     * @psalm-suppress TooManyTemplateParams
     */
    public function testClientCreatingThrowsException(): void
    {
        /** @var StreamInterface $streamMock */
        $streamMock = $this->mockStreamInterface('test')
            ->reveal();

        /** @var RequestInterface $requestMock */
        $requestMock = $this->mockRequestInterface('GET', '/test', $streamMock, [])
            ->reveal();

        /** @var ResponseInterface $responseMock */
        $responseMock = $this->mockResponseInterface(200, $streamMock, [])
            ->reveal();

        /** @var RequestFactoryInterface $requestFactoryMock */
        $requestFactoryMock = $this->mockRequestFactoryInterface($requestMock)
            ->reveal();

        /** @var ClientInterface $clientMock */
        $clientMock = $this->mockClientInterface($requestMock, $responseMock)
            ->reveal();

        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument */
        $this->getCurrencylayerClient(
            $clientMock,
            $requestFactoryMock,
            'wrong-scheme'
        );
    }

    /**
     * @throws CurrencylayerException
     * @throws InvalidArgumentException
     * @psalm-suppress TooManyArguments
     */
    public function testClientPerformThrowsException(): void
    {
        /** @var StreamInterface $streamMock */
        $streamMock = $this->mockStreamInterface('[1,2,3]')
            ->reveal();

        /** @var RequestInterface $requestMock */
        $requestMock = $this->mockRequestInterface('GET', '/test', $streamMock, [])
            ->reveal();

        /** @var RequestFactoryInterface $requestFactoryMock */
        $requestFactoryMock = $this->mockRequestFactoryInterface($requestMock)
            ->reveal();

        $clientProphecyObj = $this->prophesize(ClientInterface::class);

        /** @var Exception $exception */
        $exception = $this->mockClientInterfaceException()->reveal();
        $clientProphecyObj
            ->sendRequest(Argument::cetera())
            ->willThrow($exception);

        /** @var ClientInterface $clientMock */
        $clientMock = $clientProphecyObj->reveal();

        $currencylayerClient = $this->getCurrencylayerClient(
            $clientMock,
            $requestFactoryMock
        );

        $apiActionProphecyObj = $this->prophesize(ActionInterface::class);
        $apiActionProphecyObj->getEndpoint()->willReturn(ActionInterface::ENDPOINT_LIVE);
        $apiActionProphecyObj->getData()->willReturn(['a' => 'b']);

        /** @var ActionInterface $apiActionMock */
        $apiActionMock = $apiActionProphecyObj->reveal();

        $this->expectException(CurrencylayerException::class);

        $currencylayerClient->perform($apiActionMock);
    }

    /**
     * @throws InvalidArgumentException
     * @psalm-suppress TooManyArguments
     * @psalm-suppress TooManyTemplateParams
     */
    public function testClientInvalidEndpointThrowsException(): void
    {
        /** @var StreamInterface $streamMock */
        $streamMock = $this->mockStreamInterface('["a", "b"]')
            ->reveal();

        /** @var RequestInterface $requestMock */
        $requestMock = $this->mockRequestInterface('GET', '/test', $streamMock, [])
            ->reveal();

        /** @var ResponseInterface $responseMock */
        $responseMock = $this->mockResponseInterface(200, $streamMock, [])
            ->reveal();

        /** @var RequestFactoryInterface $requestFactoryMock */
        $requestFactoryMock = $this->mockRequestFactoryInterface($requestMock)
            ->reveal();

        /** @var ClientInterface $clientMock */
        $clientMock = $this->mockClientInterface($requestMock, $responseMock)
            ->reveal();

        $currencylayerClient = $this->getCurrencylayerClient(
            $clientMock,
            $requestFactoryMock,
        );

        $apiActionProphecyObj = $this->prophesize(ActionInterface::class);
        $apiActionProphecyObj->getEndpoint()->willReturn('wrong-endpoint');
        $apiActionProphecyObj->getData()->willReturn(['a' => 'b']);
        $apiActionProphecyObj->getResponseFactory()->willReturn(new ListResponseFactory());

        /** @var ActionInterface $apiActionMock */
        $apiActionMock = $apiActionProphecyObj->reveal();

        $this->expectException(CurrencylayerException::class);
        $this->expectErrorMessage('Unexpected response from currencylayer API');

        $currencylayerClient->perform($apiActionMock);
    }

    /**
     * @dataProvider clientSuccessfulResponseData
     * @param array $responseData
     * @param array $expectedData
     * @throws CurrencylayerException
     * @throws InvalidArgumentException
     * @throws JsonException
     *
     * @psalm-param class-string<ResponseFactoryInterface> $responseFactoryClass
     * @psalm-param ActionInterface::* $endpoint
     *
     * @psalm-suppress TooManyTemplateParams
     * @psalm-suppress TooManyArguments
     */
    public function testClientSuccessfulRequest(
        string $endpoint,
        array $responseData,
        string $responseFactoryClass,
        array $expectedData
    ): void {
        /** @var StreamInterface $streamMock */
        $streamMock = $this->mockStreamInterface(json_encode($responseData, JSON_THROW_ON_ERROR))
            ->reveal();

        /** @var RequestInterface $requestMock */
        $requestMock = $this->mockRequestInterface('GET', $endpoint, null, [])
            ->reveal();

        /** @var ResponseInterface $responseMock */
        $responseMock = $this->mockResponseInterface(200, $streamMock, [])
            ->reveal();

        /** @var RequestFactoryInterface $requestFactoryMock */
        $requestFactoryMock = $this->mockRequestFactoryInterface($requestMock)
            ->reveal();

        /** @var ClientInterface $clientMock */
        $clientMock = $this->mockClientInterface($requestMock, $responseMock)
            ->reveal();

        $currencylayerClient = $this->getCurrencylayerClient(
            $clientMock,
            $requestFactoryMock
        );

        $apiActionProphecyObj = $this->prophesize(ActionInterface::class);
        $apiActionProphecyObj->getEndpoint()->willReturn($endpoint);
        $apiActionProphecyObj->getData()->willReturn([]);
        $apiActionProphecyObj->getResponseFactory()->willReturn(new $responseFactoryClass());

        /** @var ActionInterface $apiActionMock */
        $apiActionMock = $apiActionProphecyObj->reveal();

        $result = $currencylayerClient->perform($apiActionMock);

        self::assertEquals($expectedData, $result->toArray());
    }

    /**
     * @return Generator
     * @psalm-suppress TooManyArguments
     */
    public function clientSuccessfulResponseData(): Generator
    {
        yield 'list-endpoint' => [
            ActionInterface::ENDPOINT_LIST,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'currencies' => [
                    Currency::UAH,
                    Currency::USD,
                    Currency::EUR,
                ],
            ],
            ListResponseFactory::class,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'currencies' => [
                    Currency::UAH,
                    Currency::USD,
                    Currency::EUR,
                ],
            ],
        ];

        yield 'live-endpoint' => [
            ActionInterface::ENDPOINT_LIVE,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'timestamp' => 1601983403,
                'source' => Currency::UAH,
                'quotes' => [
                    Currency::UAH . Currency::USD => 1.123456,
                ],
            ],
            LiveResponseFactory::class,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'timestamp' => 1601983403,
                'source' => Currency::UAH,
                'quotes' => [
                    Currency::UAH . Currency::USD => 1.123456,
                ],
            ],
        ];

        yield 'change-endpoint' => [
            ActionInterface::ENDPOINT_CHANGE,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'change' => true,
                'start_date' => '2020-01-01',
                'end_date' => '2020-01-02',
                'source' => Currency::UAH,
                'quotes' => [
                    Currency::UAH . Currency::USD => [
                        'start_rate' => 1.123456,
                        'end_rate' => 1.3456,
                        'change' => -0.222,
                        'change_pct' => 3.57,
                    ],
                ],
            ],
            ChangeResponseFactory::class,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'change' => true,
                'start_date' => '2020-01-01',
                'end_date' => '2020-01-02',
                'source' => Currency::UAH,
                'quotes' => [
                    new ChangeQuote(
                        Currency::UAH . Currency::USD,
                        new ChangeInfo(
                            1.123456,
                            1.3456,
                            -0.222,
                            3.57
                        )
                    ),
                ],
            ],
        ];

        yield 'convert-endpoint' => [
            ActionInterface::ENDPOINT_CONVERT,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'query' => [
                    'from' => Currency::UAH,
                    'to' => Currency::USD,
                    'amount' => 12,
                ],
                'info' => [
                    'timestamp' => 1601983403,
                    'quote' => 1.123456,
                ],
                'result' => 1.123457,
                'historical' => true,
                'date' => '2020-01-01',
            ],
            ConvertResponseFactory::class,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'query' => new Query(Currency::UAH, Currency::USD, 12),
                'info' => new CurrencylayerInfo(1601983403, 1.123456),
                'result' => 1.123457,
                'historical' => true,
                'date' => '2020-01-01',
            ],
        ];

        yield 'historical-endpoint' => [
            ActionInterface::ENDPOINT_HISTORICAL,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'historical' => true,
                'date' => '2020-01-01',
                'timestamp' => 1601983403,
                'source' => Currency::UAH,
                'quotes' => [
                    Currency::UAH . Currency::USD => 1.123456,
                    Currency::UAH . Currency::EUR => 1.876543,
                ],
            ],
            HistoricalResponseFactory::class,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'timestamp' => 1601983403,
                'historical' => true,
                'date' => '2020-01-01',
                'source' => Currency::UAH,
                'quotes' => [
                    Currency::UAH . Currency::USD => 1.123456,
                    Currency::UAH . Currency::EUR => 1.876543,
                ],
            ],
        ];

        yield 'timeframe-endpoint' => [
            ActionInterface::ENDPOINT_TIMEFRAME,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'timeframe' => true,
                'start_date' => '2020-01-01',
                'end_date' => '2020-01-02',
                'source' => Currency::UAH,
                'quotes' => [
                    '2020-01-01' => [
                        Currency::UAH . Currency::USD => 1.123456,
                        Currency::UAH . Currency::EUR => 1.876543,
                    ],
                    '2020-01-02' => [
                        Currency::UAH . Currency::USD => 1.123457,
                        Currency::UAH . Currency::EUR => 1.876541,
                    ],
                ],
            ],
            TimeframeResponseFactory::class,
            [
                'success' => true,
                'terms' => 'test_terms',
                'privacy' => 'test_privacy',
                'timeframe' => true,
                'start_date' => '2020-01-01',
                'end_date' => '2020-01-02',
                'source' => Currency::UAH,
                'quotes' => [
                    '2020-01-01' => [
                        Currency::UAH . Currency::USD => 1.123456,
                        Currency::UAH . Currency::EUR => 1.876543,
                    ],
                    '2020-01-02' => [
                        Currency::UAH . Currency::USD => 1.123457,
                        Currency::UAH . Currency::EUR => 1.876541,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param ClientInterface $httpClient
     * @param RequestFactoryInterface $requestFactory
     * @return CurrencylayerClient
     * @throws InvalidArgumentException
     *
     * @psalm-param HttpSchema::* $scheme
     */
    private function getCurrencylayerClient(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        $scheme = HttpSchema::SCHEMA_HTTP
    ): CurrencylayerClient {
        return new CurrencylayerClient(
            $httpClient,
            $requestFactory,
            self::TEST_API_KEY,
            $scheme
        );
    }
}
