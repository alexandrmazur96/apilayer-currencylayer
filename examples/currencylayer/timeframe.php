<?php

use Apilayer\Currencylayer\Actions\Timeframe as TimeframeAction;
use Apilayer\Currencylayer\CurrencylayerClient;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Timeframe as TimeframeResponse;
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

$startDate = new DateTimeImmutable('2020-01-01');
$endDate = new DateTimeImmutable('2020-01-25');
$sourceCurrency = Currency::UAH;
$currencies = [Currency::USD, Currency::EUR];

try {
    $timeframeAction = new TimeframeAction(
        $startDate,
        $endDate,
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
    /** @var TimeframeResponse $timeframeResponse */
    $timeframeResponse = $currencylayerClient->perform($timeframeAction);
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
$success = $timeframeResponse->isSuccess();

/*
 * Links to terms & privacy.
 */
$terms = $timeframeResponse->getTerms();
$privacy = $timeframeResponse->getPrivacy();

/* Indicates that request was performed to /timeframe endpoint. */
$isTimeframe = $timeframeResponse->isTimeframe();

/*
 * Methods below just reflect the same request parameters.
 */
$startDate = $timeframeResponse->getStartDate();
$endDate = $timeframeResponse->getEndDate();
$source = $timeframeResponse->getSource();

/*
 * Contain sub-objects with exchange rate data per day in your time frame.
 */
$timeframeQuotes = $timeframeResponse->getQuotes();

foreach ($timeframeQuotes as $date => $quotes) {
    echo 'For date ', $date, PHP_EOL;
    foreach ($quotes as $currencyPair => $exchangeRate) {
        echo 'For currency pair ', $currencyPair, ' exchange rate is ', $exchangeRate, PHP_EOL;
    }
}

/* All response classes implements JsonSerializable interface. */
$jsonRepresentation = json_encode($timeframeResponse, JSON_THROW_ON_ERROR);
