<?php

use Apilayer\Currencylayer\Actions\Live as LiveAction;
use Apilayer\Currencylayer\CurrencylayerClient;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Live as LiveResponse;
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

$sourceCurrency = Currency::UAH;
$currencies = [Currency::USD, Currency::EUR];

try {
    $liveAction = new LiveAction(
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
    /** @var LiveResponse $liveResponse */
    $liveResponse = $currencylayerClient->perform($liveAction);
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
$success = $liveResponse->isSuccess();

/*
 * Links to terms & privacy.
 */
$terms = $liveResponse->getTerms();
$privacy = $liveResponse->getPrivacy();

/* Source here just reflect the same request parameter. */
$liveResponse->getSource();

/* Timestamp when request was performed. */
$liveResponse->getTimestamp();

/* Contains all exchange rate values, consisting of the currency pairs and their respective conversion rates. */
$liveResponse->getQuotes();

/* All response classes implements JsonSerializable interface. */
$jsonRepresentation = json_encode($liveResponse, JSON_THROW_ON_ERROR);
