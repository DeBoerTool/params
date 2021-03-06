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
            'join_uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'value' => rand(1, 999),
            'arguments' => [
                'min' => 0,
                'max' => 99,
            ],
        ];

        $field = Field::hydrate($array);

        $this->assertSame($array['uuid'], $field->uuid());
        $this->assertSame($array['join_uuid'], $field->joinUuid());
        $this->assertSame($array['name'], $field->name());
        $this->assertSame($array['type'], $field->type());
        $this->assertSame($array['value'], $field->value());
        $this->assertSame($array['arguments'], $field->arguments());
        $this->assertSame($array, $field->toArray());

        unset($array['value']);

        $field = Field::hydrate($array);

        $this->assertNull($field->value());
    }

    /** @test */
    public function getting_the_composite_key (): void
    {
        $joinUuid = $this->rs(32);
        $uuid = $this->rs(32);

        $array = [
            'uuid' => $uuid,
            'join_uuid' => $joinUuid,
            'name' => $this->rs(16),
            'type' => $this->rs(16),
        ];

        $field = Field::hydrate($array);

        $this->assertSame(
            implode('_', [$joinUuid, $uuid]),
            $field->compositeKey(),
        );
    }

    /** @test */
    public function failing_with_invalid_value_type (): void
    {
        $this->makeField(['value' => 'string']);
        $this->makeField(['value' => 1]);
        $this->makeField(['value' => 1.1]);
        $this->makeField(['value' => true]);
        $this->makeField(['value' => null]);

        $this->expectException(InvalidArgumentException::class);

        $this->makeField(['value' => []]);
    }

    /** @test */
    public function serializing_to_json (): void
    {
        $field = $this->makeField();

        $json = json_encode($field);

        $this->assertSame($field->toArray(), json_decode($json, true));
    }

    /** @test */
    public function mutating (): void
    {
        $field = $this->makeField();

        $newValue = $this->rs(16);
        $mutated = $field->mutate($newValue);

        $this->assertNotSame($field, $mutated);
        $this->assertNotSame($field->value(), $mutated->value());
        $this->assertSame($field->uuid(), $mutated->uuid());
        $this->assertSame($mutated->value(), $newValue);
    }

    /** @test */
    public function is_null (): void
    {
        $field1 = $this->makeField();
        $field2 = $this->makeField(['value' => null]);

        $this->assertFalse($field1->isNull());
        $this->assertTrue($field2->isNull());
    }
}
