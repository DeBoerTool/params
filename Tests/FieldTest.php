<?php

namespace Dbt\Params\Tests;

use Dbt\Params\Field;
use InvalidArgumentException;

/**
 * @covers \Dbt\Params\Field
 */
class FieldTest extends UnitTestCase
{
    /** @test */
    public function hydrating (): void
    {
        $array = [
            'uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'value' => rand(1, 999),
        ];

        $field = Field::hydrate($array);

        $this->assertSame($array['uuid'], $field->uuid());
        $this->assertSame($array['name'], $field->name());
        $this->assertSame($array['type'], $field->type());
        $this->assertSame($array['value'], $field->value());
    }

    /** @test */
    public function failing_with_invalid_value_type (): void
    {
        new Field('', '', '', '');
        new Field('', '', '', 1);
        new Field('', '', '', 1.1);
        new Field('', '', '', true);

        $this->expectException(InvalidArgumentException::class);

        /** @noinspection PhpParamsInspection */
        new Field('', '', '', []);
    }

    /** @test */
    public function serializing_to_json (): void
    {
        $array = [
            'uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'value' => rand(1, 999),
        ];

        $field = Field::hydrate($array);

        $json = json_encode($field);

        $this->assertSame($array, json_decode($json, true));
    }
}
