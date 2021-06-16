<?php

namespace Dbt\Params\Tests;

use Dbt\Params\Exceptions\NotFoundException;

/**
 * @covers \Dbt\Params\Exceptions\NotFoundException
 */
class NotFoundExceptionTest extends UnitTestCase
{
    /** @test */
    public function message_for_param (): void
    {
        try {
            throw NotFoundException::param();
        } catch (NotFoundException $exception) {
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
            throw NotFoundException::field();
        } catch (NotFoundException $exception) {
            $this->assertStringContainsString(
                'Field',
                $exception->getMessage()
            );
        }
    }
}
