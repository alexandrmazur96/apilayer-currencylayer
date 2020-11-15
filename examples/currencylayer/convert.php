<?php

use Apilayer\Currencylayer\Actions\Convert as ConvertAction;
use Apilayer\Currencylayer\CurrencylayerClient;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Convert as ConvertResponse;
use Apilayer\Currencylayer\Enums\Currency;
use Apilayer\Currencylayer\Enums\HttpSchema;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;

require_once __DIR__ . '/../../vendor/autoload.php';

$currencylayerApiKey = '<your API key>';
$psr18HttpClient = Psr18ClientDiscovery::find();
$psr17RequestFactory = Psr17FactoryDiscovery::findRequestFactory();

try {
    $currencylayerClient = new CurrencylayerClient(
        $psr18HttpClient,
        $psr17RequestFactory,
        $currencylayerApiKey,
        HttpSchema::SCHEMA_HTTP
    );
} catch (InvalidArgumentException $e) {
    /**
     * This exception may be caused by passing wrong HTTP schema.
     * Use {@see HttpSchema} constants for available schemas values to avoid this exception.
     */
    echo 'Failed to create currencylayer client - ', $e->getMessage(), PHP_EOL;
    die(1);
}

$fromCurrency = Currency::USD;
$toCurrency = Currency::UAH;
$amount = 12.25;
$forDate = new DateTimeImmutable('2020-01-01');

try {
    $convertAction = new ConvertAction(
        $fromCurrency,
        $toCurrency,
        $amount,
        $forDate // optional
    );
} catch (InvalidArgumentException $e) {
    /**
     * This exception may be caused by passing wrong parameters to the constructor.
     * See {@see InvalidArgumentException::getMessage()} for more details.
     */
    echo 'Failed to create action - ', $e->getMessage(), PHP_EOL;
    die(1);
}

try {
    /** @var ConvertResponse $convertResponse */
    $convertResponse = $currencylayerClient->perform($convertAction);
} catch (CurrencylayerException $e) {
    /**
     * This exception may be caused by the different reasons:
     * - HTTP client throw an error (exception would be caught and re-thrown
     *          by CurrencylayerException with same parameters);
     * - Unable to decode response JSON (Unlikely);
     * - API respond without 'success' key - in this case check exception
     *          message about what exactly API respond;
     * - API respond with {success:false}. Check exception message and code.
     */
    echo 'Failed to perform API request - ', $e->getMessage(), PHP_EOL;
    die(1);
}

/* Always true here. */
$success = $convertResponse->isSuccess();

/*
 * Links to terms & privacy.
 */
$terms = $convertResponse->getTerms();
$privacy = $convertResponse->getPrivacy();

/*
 * Methods below just reflect the same request parameters.
 */
$convertResponse->getInfo();
$convertResponse->getQuery();

$conversationResult = $convertResponse->getResult();

/* All response classes implements JsonSerializable interface. */
$jsonRepresentation = json_encode($convertResponse, JSON_THROW_ON_ERROR);
