<?php

use Apilayer\Currencylayer\Actions\Change as ChangeAction;
use Apilayer\Currencylayer\CurrencylayerClient;
use Apilayer\Currencylayer\Exceptions\CurrencylayerException;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Change as ChangeResponse;
use Apilayer\Currencylayer\ValueObjects\ChangeInfo;
use Apilayer\Currencylayer\ValueObjects\ChangeQuote;
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
    $changeAction = new ChangeAction(
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
    /** @var ChangeResponse $changeResponse */
    $changeResponse = $currencylayerClient->perform($changeAction);
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
$success = $changeResponse->isSuccess();

/*
 * Links to terms & privacy.
 */
$terms = $changeResponse->getTerms();
$privacy = $changeResponse->getPrivacy();

/* Indicates that request was performed to /change endpoint. */
$isChange = $changeResponse->isChange();

/*
 * Methods below just reflect the same request parameters.
 */
$startDate = $changeResponse->getStartDate();
$endDate = $changeResponse->getEndDate();
$source = $changeResponse->getSource();

/*
 * The quotes object will contain one sub-object with exchange rate data per currency pair.
 */
$quotes = $changeResponse->getQuotes();

/** @var ChangeQuote $quote */
foreach ($quotes as $quote) {
    /* Left part of the currency pair. */
    $currencyFrom = $quote->getCurrencyFrom();
    /* Right part of the currency pair. */
    $currencyTo = $quote->getCurrencyTo();
    $currencyPair = $quote->getCurrencyPair();

    /** @var ChangeInfo $changeInfo */
    $changeInfo = $quote->getChangeInfo();

    /* The respective currency's exchange rate at the beginning of the specified period. */
    $startRate = $changeInfo->getStartRate();
    /* The respective currency's exchange rate at the end of the specified period. */
    $endRate = $changeInfo->getEndRate();
    /* The margin between the currency's `start_rate` and `end_rate`. */
    $change = $changeInfo->getChange();
    /* The currency's percentage change within the specified time frame. */
    $changePercent = $changeInfo->getChangePct();

    $changeInfoJson = json_encode($changeInfo, JSON_THROW_ON_ERROR);
}

/* All response classes implements JsonSerializable interface. */
$jsonRepresentation = json_encode($changeResponse, JSON_THROW_ON_ERROR);
