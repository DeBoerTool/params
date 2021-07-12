<?php

namespace Dbt\Params\Tests;

use Dbt\Params\Exceptions\NotFoundException;
use Dbt\Params\Field;
use Dbt\Params\FieldMap;

/**
 * @covers \Dbt\Params\FieldMap
 */
class FieldMapTest extends UnitTestCase
{
    /** @test */
    public function hydrating (): void
    {
        $joinUuid = $this->rs(32);

        $array = [
            $joinUuid => [
                'uuid' => $this->rs(32),
                'join_uuid' => $joinUuid,
                'name' => $this->rs(16),
                'type' => $this->rs(16),
                'value' => 0,
                'arguments' => [],
            ],
        ];

        $map = FieldMap::hydrate($array);

        $this->assertSame($map->toArray(), $array);
    }

    /** @test */
    public function serializing (): void
    {
        $joinUuid = $this->rs(32);

        $array = [
            $joinUuid => [
                'uuid' => $this->rs(32),
                'join_uuid' => $joinUuid,
                'name' => $this->rs(16),
                'type' => $this->rs(16),
                'value' => 0,
                'arguments' => [],
            ],
        ];

        $map = FieldMap::hydrate($array);

        $this->assertSame($array, json_decode(json_encode($map), true));
    }

    /** @test */
    public function filtering (): void
    {
        $map = new FieldMap([
            $this->makeField(),
            $this->makeField(),
            $this->makeField(['name' => 'my-name']),
        ]);

        $filtered = $map->filter(
            fn (string $_, Field $field): bool => str_contains(
                $field->name(), 'my-'
            )
        );

        $this->assertNotSame($map, $filtered);
        foreach ($filtered as $field) {
            $this->assertTrue(str_contains($field->name(), 'my-'));
        }
    }

    /** @test */
    public function mapping (): void
    {
        $map = new FieldMap([
            $this->makeField(['name' => '1']),
            $this->makeField(['name' => '2']),
            $this->makeField(['name' => '3']),
        ]);

        $mapped = $map->items(
            fn (string $_, Field $field): string => $field->name()
        );

        $this->assertIsArray($mapped);
        $this->assertSame(['1', '2', '3'], $mapped);
    }

    /** @test */
    public function mapping_with_keys (): void
    {
        $map = new FieldMap([
            $this->makeField(['type' => '1', 'name' => '4']),
            $this->makeField(['type' => '2', 'name' => '5']),
            $this->makeField(['type' => '3', 'name' => '6']),
        ]);

        $mapped = $map->mapWithKeys(
            fn (string $_, Field $field): array => [
                $field->type() => $field->name()
            ]
        );

        $this->assertIsArray($mapped);
        $this->assertSame(['1' => '4', '2' => '5', '3' => '6'], $mapped);
    }

    /** @test */
    public function reducing (): void
    {
        $map = new FieldMap([
            $this->makeField(['type' => '1', 'name' => '4']),
            $this->makeField(['type' => '2', 'name' => '5']),
            $this->makeField(['type' => '3', 'name' => '6']),
        ]);

        $reduced = $map->reduce(
            fn (int $acc, string $_, Field $field): int => $acc
                + (int) $field->type(),
            1,
        );

        $this->assertIsInt($reduced);
        $this->assertSame(7, $reduced);
    }

    /** @test */
    public function finding (): void
    {
        $map = new FieldMap([
            $this->makeField(['type' => '1', 'name' => '4']),
            $this->makeField(['type' => '2', 'name' => '5']),
            $this->makeField(['type' => '3', 'name' => '6']),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $found = $map->find(fn (string $_, Field $field): bool =>
            $field->name() === '5'
        );

        $this->assertSame('5', $found->name());
    }

    /** @test */
    public function not_finding (): void
    {
        $map = new FieldMap();

        $this->expectException(NotFoundException::class);

        $map->find(fn (string $_, Field $field): bool =>
            $field->name() === '2'
        );
    }

    /** @test */
    public function getting_a_member (): void
    {
        $field = $this->makeField();

        $map = new FieldMap([
            $this->makeField(),
            $field,
            $this->makeField(),
        ]);

        $this->assertSame(
            $map->get($field->joinUuid()),
            $field,
        );
    }

    /** @test */
    public function mutating_a_member (): void
    {
        $field = $this->makeField();

        $map = new FieldMap([
            $this->makeField(),
            $field,
            $this->makeField(),
        ]);

        $this->assertSame($field, $map->get($field->joinUuid()));

        $newValue = $this->rs(16);
        $map->mutate($field->joinUuid(), $newValue);

        $this->assertNotSame($field, $map->get($field->joinUuid()));
        $this->assertSame($newValue, $map->get($field->joinUuid())->value());
        $this->assertSame(
            $field->uuid(),
            $map->get($field->joinUuid())->uuid()
        );
    }

    /** @test */
    public function iterating (): void
    {
        $map = new FieldMap([
            $this->makeField(),
            $this->makeField(),
        ]);

        foreach ($map as $key => $field) {
            $this->assertSame($key, $field->joinUuid());
        }
    }

    /** @test */
    public function offset_get (): void
    {
        $field = $this->makeField();

        $map = new FieldMap([
            $this->makeField(),
            $field,
        ]);

        $this->assertSame(
            $field,
            $map[$field->joinUuid()],
        );
    }

    /** @test */
    public function offset_exists (): void
    {
        $field = $this->makeField();

        $map = new FieldMap([
            $this->makeField(),
            $field,
        ]);

        $this->assertTrue(isset($map[$field->joinUuid()]));
        $this->assertFalse(isset($map['some-other-key']));
    }

    /** @test */
    public function offset_set (): void
    {
        $field = $this->makeField();

        $map = new FieldMap();

        $map[$field->joinUuid()] = $field;

        $this->assertSame($map[$field->joinUuid()], $field);
    }

    /** @test */
    public function offset_unset (): void
    {
        $field = $this->makeField();

        $map = new FieldMap([$field]);

        $this->assertCount(1, $map);

        unset($map[$field->joinUuid()]);

        $this->assertCount(0, $map);
    }
}
