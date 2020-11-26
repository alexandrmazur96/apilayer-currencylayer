<?php

namespace Apilayer\Currencylayer\Actions;

use Apilayer\Currencylayer\Responses\Factories\LiveResponseFactory;
use Apilayer\Currencylayer\Exceptions\InvalidArgumentException;
use Apilayer\Currencylayer\Responses\Factories\ResponseFactoryInterface;
use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-type _LiveActionData=array{
 *      source?:Currency::*,
 *      currencies?:list<Currency::*>
 * }
 */
class Live implements ActionInterface
{
    use ActionAssertTrait;

    /** @psalm-var Currency::*|null */
    private ?string $source;

    /** @psalm-var list<Currency::*>|null */
    private ?array $currencies;

    /**
     * @param string|null $source
     * @param string[]|null $currencies
     * @throws InvalidArgumentException
     */
    public function __construct(
        ?string $source = null,
        ?array $currencies = null
    ) {
        if ($source !== null) {
            $this->assertSourceCurrency($source);
            /** @psalm-var Currency::* $source */
        }
        if ($currencies !== null) {
            $this->assertCurrencies($currencies);
            /** @psalm-var list<Currency::*> $currencies */
        }

        $this->source = $source;
        $this->currencies = $currencies;
    }

    /** @psalm-return ActionInterface::ENDPOINT_LIVE */
    public function getEndpoint(): string
    {
        return ActionInterface::ENDPOINT_LIVE;
    }

    /**
     * @psalm-return _LiveActionData
     */
    public function getData(): array
    {
        $request = [];

        if ($this->source !== null) {
            $request['source'] = $this->source;
        }

        if ($this->currencies !== null && !empty($this->currencies)) {
            $request['currencies'] = $this->currencies;
        }

        return $request;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return new LiveResponseFactory();
    }
}
