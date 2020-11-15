<?php

namespace Apilayer\Tests\Currencylayer\Actions;

use Apilayer\Currencylayer\Actions\ActionInterface;
use Apilayer\Currencylayer\Actions\ListAction;
use Apilayer\Tests\TestCase;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class ListTest extends TestCase
{
    public function testGetEndpoint(): void
    {
        $list = new ListAction();
        self::assertEquals(ActionInterface::ENDPOINT_LIST, $list->getEndpoint());
    }

    public function testGetData(): void
    {
        $list = new ListAction();
        self::assertEquals([], $list->getData());
    }
}
