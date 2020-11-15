<?php

use Apilayer\Currencylayer\Actions\ListAction;
use Apilayer\Currencylayer\CurrencylayerClient;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\ListResponse;
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

$listAction = new ListAction();

try {
    /** @var ListResponse $listResponse */
    $listResponse = $currencylayerClient->perform($listAction);
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
$success = $listResponse->isSuccess();

/*
 * Links to terms & privacy.
 */
$terms = $listResponse->getTerms();
$privacy = $listResponse->getPrivacy();

/* List of acceptable by Currencylayer API currencies. E.g., ["UAH", "USD", "EUR", ...] */
$availableCurrenciesList = $listResponse->getCurrencies();

/* All response classes implements JsonSerializable interface. */
$jsonRepresentation = json_encode($listResponse, JSON_THROW_ON_ERROR);
