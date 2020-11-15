<?php

use Apilayer\Currencylayer\Actions\Historical as HistoricalAction;
use Apilayer\Currencylayer\CurrencylayerClient;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Historical as HistoricalResponse;
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

$date = new DateTimeImmutable('2020-01-01');
$sourceCurrency = Currency::UAH;
$currencies = [Currency::USD, Currency::EUR];

try {
    $historicalAction = new HistoricalAction(
        $date,
        $sourceCurrency, //optional
        $currencies //optional
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
    /** @var HistoricalResponse $historicalResponse */
    $historicalResponse = $currencylayerClient->perform($historicalAction);
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
$success = $historicalResponse->isSuccess();

/*
 * Links to terms & privacy.
 */
$terms = $historicalResponse->getTerms();
$privacy = $historicalResponse->getPrivacy();

/* Indicates that request was performed to /historical endpoint. */
$isHistorical = $historicalResponse->isHistorical();

/* Source here just reflect the same request parameter. */
$source = $historicalResponse->getSource();

/* Timestamp when request was performed. */
$timestamp = $historicalResponse->getTimestamp();

/* All available exchange rates for your specified date. */
$quotes = $historicalResponse->getQuotes();

/* All response classes implements JsonSerializable interface. */
$jsonRepresentation = json_encode($historicalResponse, JSON_THROW_ON_ERROR);
