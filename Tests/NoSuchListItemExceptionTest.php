<?php

namespace Dbt\Params\Tests;

use Dbt\Params\Exceptions\NoSuchListItemException;

/**
 * @covers \Dbt\Params\Exceptions\NoSuchListItemException
 */
class NoSuchListItemExceptionTest extends UnitTestCase
{
    /** @test */
    public function message_for_param (): void
    {
        try {
            throw NoSuchListItemException::param();
        } catch (NoSuchListItemException $exception) {
            $this->assertStringContainsString(
                'Param',
                $exception->getMessage()
            );
        }
    }
    /** @test */
    public function message_for_field (): void
    {
        try {
            throw NoSuchListItemException::field();
        } catch (NoSuchListItemException $exception) {
            $this->assertStringContainsString(
                'Field',
                $exception->getMessage()
            );
        }
    }
}
