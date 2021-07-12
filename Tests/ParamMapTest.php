<?php

namespace Dbt\Params\Tests;

use Dbt\Params\Exceptions\NotFoundException;
use Dbt\Params\ParamMap;
use Dbt\Params\Param;

/**
 * @covers \Dbt\Params\ParamMap
 */
class ParamMapTest extends UnitTestCase
{
    /** @test */
    public function hydrating (): void
    {
        $uuid = $this->rs(32);
        $joinUuid = $this->rs(32);
        $name = $this->rs(16);
        $type = $this->rs(16);

        $mapArray = [
            $joinUuid => [
                'uuid' => $uuid,
                'join_uuid' => $joinUuid,
                'name' => $name,
                'type' => $type,
                'fields' => [],
            ],
        ];

        $listArray = [
            [
                'uuid' => $uuid,
                'join_uuid' => $joinUuid,
                'name' => $name,
                'type' => $type,
                'fields' => [],
            ],
        ];

        $map = ParamMap::hydrate($mapArray);
        $mapFromList = ParamMap::hydrate($listArray);

        $this->assertSame($map->toArray(), $mapArray);
        $this->assertSame($map->toArray(), $mapFromList->toArray());
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
                'fields' => [],
            ],
        ];

        $map = ParamMap::hydrate($array);

        $this->assertSame($array, json_decode(json_encode($map), true));
    }

    /** @test */
    public function filtering (): void
    {
        $map = new ParamMap([
            $this->makeParam(),
            $this->makeParam(),
            $this->makeParam(['name' => 'my-name']),
        ]);

        $filtered = $map->filter(
            fn (string $_, Param $param): bool => str_contains(
                $param->name(), 'my-'
            )
        );

        $this->assertNotSame($map, $filtered);
        foreach ($filtered as $param) {
            $this->assertTrue(str_contains($param->name(), 'my-'));
        }
    }

    /** @test */
    public function mapping (): void
    {
        $map = new ParamMap([
            $this->makeParam(['name' => '1']),
            $this->makeParam(['name' => '2']),
            $this->makeParam(['name' => '3']),
        ]);

        $mapped = $map->items(
            fn (string $_, Param $param): string => $param->name()
        );

        $this->assertIsArray($mapped);
        $this->assertSame(['1', '2', '3'], $mapped);
    }

    /** @test */
    public function mapping_with_keys (): void
    {
        $map = new ParamMap([
            $this->makeParam(['type' => '1', 'name' => '4']),
            $this->makeParam(['type' => '2', 'name' => '5']),
            $this->makeParam(['type' => '3', 'name' => '6']),
        ]);

        $mapped = $map->mapWithKeys(
            fn (string $_, Param $param): array => [
                $param->type() => $param->name()
            ]
        );

        $this->assertIsArray($mapped);
        $this->assertSame(['1' => '4', '2' => '5', '3' => '6'], $mapped);
    }

    /** @test */
    public function reducing (): void
    {
        $map = new ParamMap([
            $this->makeParam(['type' => '1', 'name' => '4']),
            $this->makeParam(['type' => '2', 'name' => '5']),
            $this->makeParam(['type' => '3', 'name' => '6']),
        ]);

        $reduced = $map->reduce(
            fn (int $acc, string $_, Param $param): int => $acc
                + (int) $param->type(),
            1,
        );

        $this->assertIsInt($reduced);
        $this->assertSame(7, $reduced);
    }

    /** @test */
    public function finding (): void
    {
        $map = new ParamMap([
            $this->makeParam(['type' => '1', 'name' => '4']),
            $this->makeParam(['type' => '2', 'name' => '5']),
            $this->makeParam(['type' => '3', 'name' => '6']),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $found = $map->find(fn (string $_, Param $param): bool =>
            $param->name() === '5'
        );

        $this->assertSame('5', $found->name());
    }

    /** @test */
    public function not_finding (): void
    {
        $map = new ParamMap();

        $this->expectException(NotFoundException::class);

        $map->find(fn (string $_, Param $param): bool =>
            $param->name() === '2'
        );
    }

    /** @test */
    public function getting_a_member (): void
    {
        $param = $this->makeParam();

        $map = new ParamMap([
            $this->makeParam(),
            $param,
            $this->makeParam(),
        ]);

        $this->assertSame(
            $map->get($param->joinUuid()),
            $param,
        );
    }

    /** @test */
    public function plucking_a_value (): void
    {
        $value = $this->rs(16);
        $field = $this->makeField(['value' => $value]);
        $param = $this->makeParam();
        $param->fields()->put($this->rs(32), $field);

        $map = new ParamMap([
            $param,
        ]);

        $plucked = $map->pluckByName($param->name(), $field->name());

        $this->assertSame($value, $plucked);
    }

    /** @test */
    public function failing_to_pluck_a_value_with_missing_param (): void
    {
        $map = new ParamMap();

        $this->expectException(NotFoundException::class);

        $map->pluckByName($this->rs(16), '');
    }

    /** @test */
    public function failing_to_pluck_a_value_with_missing_field (): void
    {
        $param = $this->makeParam();
        $map = new ParamMap([
            $param,
        ]);

        $this->expectException(NotFoundException::class);

        $map->pluckByName($param->name(), $this->rs(16));
    }

    /** @test */
    public function getting_a_flattened_values_array (): void
    {
        $field1 = [
            'uuid' => $this->rs(32),
            'join_uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'value' => 99,
        ];

        $field2 = [
            'uuid' => $this->rs(32),
            'join_uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'value' => 98,
        ];

        $param = [
            'uuid' => $this->rs(32),
            'join_uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'fields' => [$field1, $field2],
        ];

        $key1 = implode('_', [
            $param['join_uuid'],
            $param['uuid'],
            $field1['join_uuid'],
            $field1['uuid'],
        ]);

        $key2 = implode('_', [
            $param['join_uuid'],
            $param['uuid'],
            $field2['join_uuid'],
            $field2['uuid'],
        ]);

        $values = [
            $key1 => 99,
            $key2 => 98
        ];

        $map = ParamMap::hydrate([$param]);

        $this->assertSame($values, $map->values());
    }

    /** @test */
    public function getting_a_list_of_fields (): void
    {
        $fields = [
            [
                'uuid' => $this->rs(32),
                'join_uuid' => $this->rs(32),
                'name' => $this->rs(16),
                'type' => $this->rs(16),
            ],
            [
                'uuid' => $this->rs(32),
                'join_uuid' => $this->rs(32),
                'name' => $this->rs(16),
                'type' => $this->rs(16),
            ]
        ];

        $array = [[
            'uuid' => $this->rs(32),
            'join_uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'fields' => $fields,
        ]];

        $map = ParamMap::hydrate($array);

        $this->assertSame($fields[0]['uuid'], $map->fieldList()[0]->uuid());
        $this->assertSame($fields[1]['uuid'], $map->fieldList()[1]->uuid());
    }

    /** @test */
    public function iterating (): void
    {
        $map = new ParamMap([
            $this->makeParam(),
            $this->makeParam(),
        ]);

        foreach ($map as $key => $param) {
            $this->assertSame($key, $param->joinUuid());
        }
    }

    /** @test */
    public function offset_get (): void
    {
        $param = $this->makeParam();

        $map = new ParamMap([
            $this->makeParam(),
            $param,
        ]);

        $this->assertSame(
            $param,
            $map[$param->joinUuid()],
        );
    }

    /** @test */
    public function offset_exists (): void
    {
        $param = $this->makeParam();

        $map = new ParamMap([
            $this->makeParam(),
            $param,
        ]);

        $this->assertTrue(isset($map[$param->joinUuid()]));
        $this->assertFalse(isset($map['some-other-key']));
    }

    /** @test */
    public function offset_set (): void
    {
        $param = $this->makeParam();

        $map = new ParamMap();

        $map[$param->joinUuid()] = $param;

        $this->assertSame($map[$param->joinUuid()], $param);
    }

    /** @test */
    public function offset_unset (): void
    {
        $param = $this->makeParam();

        $map = new ParamMap([$param]);

        $this->assertCount(1, $map);

        unset($map[$param->joinUuid()]);

        $this->assertCount(0, $map);
    }
}
