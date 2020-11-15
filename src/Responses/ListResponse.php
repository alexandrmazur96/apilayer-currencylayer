<?php

namespace Apilayer\Currencylayer\Responses;

use Apilayer\Currencylayer\Enums\Currency;

/**
 * @psalm-type _ListResponseData=array{
 *      success:bool,
 *      terms:string,
 *      privacy:string,
 *      currencies:list<Currency::*>
 * }
 */
class ListResponse extends DataAbstractResponse
{
    /** @psalm-var list<Currency::*> */
    private array $currencies;

    /**
     * @param bool $success
     * @param string $terms
     * @param string $privacy
     *
     * @psalm-param list<Currency::*> $currencies
     */
    public function __construct(
        bool $success,
        string $terms,
        string $privacy,
        array $currencies
    ) {
        parent::__construct($success, $terms, $privacy);
        $this->currencies = $currencies;
    }

    /** @psalm-return list<Currency::*> */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    /**
     * @psalm-return _ListResponseData
     */
    public function toArray(): array
    {
        return [
            'success' => $this->isSuccess(),
            'terms' => $this->getTerms(),
            'privacy' => $this->getPrivacy(),
            'currencies' => $this->getCurrencies(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
