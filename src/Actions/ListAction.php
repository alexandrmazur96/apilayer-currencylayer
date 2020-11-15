<?php

namespace Apilayer\Currencylayer\Actions;

use Apilayer\Currencylayer\Responses\Factories\ListResponseFactory;
use Apilayer\Currencylayer\Responses\Factories\ResponseFactoryInterface;

/**
 * @psalm-immutable
 */
class ListAction implements ActionInterface
{
    /** @psalm-return ActionInterface::ENDPOINT_LIST */
    public function getEndpoint(): string
    {
        return ActionInterface::ENDPOINT_LIST;
    }

    public function getData(): array
    {
        return [];
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return new ListResponseFactory();
    }
}
