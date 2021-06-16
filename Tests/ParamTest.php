<?php

namespace Dbt\Params\Tests;

use Dbt\Params\Param;

/**
 * @covers \Dbt\Params\Param
 */
class ParamTest extends UnitTestCase
{
    /** @test */
    public function hydrating (): void
    {
        $joinUuid = $this->rs(32);

        $array = [
            'uuid' => $this->rs(32),
            'join_uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'fields' => [
                $joinUuid => [
                    'uuid' => $this->rs(32),
                    'join_uuid' => $joinUuid,
                    'name' => $this->rs(16),
                    'type' => $this->rs(16),
                    'value' => rand(1, 999),
                    'arguments' => [],
                ]
            ]
        ];

        $param = Param::hydrate($array);

        $this->assertSame($array, $param->toArray());
        $this->assertSame($array['uuid'], $param->uuid());
        $this->assertSame($array['name'], $param->name());
        $this->assertSame($array['type'], $param->type());
        $this->assertSame(
            $array['fields'][$joinUuid],
            $param->fields()->get($joinUuid)->jsonSerialize(),
        );
    }

    /** @test */
    public function getting_the_composite_key (): void
    {
        $uuid = $this->rs(32);
        $joinUuid = $this->rs(32);

        $array = [
            'uuid' => $uuid,
            'join_uuid' => $joinUuid,
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'fields' => []
        ];

        $param = Param::hydrate($array);

        $this->assertSame(
            implode('_', [$joinUuid, $uuid]),
            $param->compositeKey(),
        );
    }

    /** @test */
    public function serializing_to_json (): void
    {
        $joinUuid = $this->rs(32);

        $array = [
            'uuid' => $this->rs(32),
            'join_uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'fields' => [
                $joinUuid => [
                    'uuid' => $this->rs(32),
                    'join_uuid' => $joinUuid,
                    'name' => $this->rs(16),
                    'type' => $this->rs(16),
                    'value' => rand(1, 999),
                    'arguments' => [],
                ]
            ]
        ];

        $param = Param::hydrate($array);

        $json = json_encode($param);

        $this->assertSame($array, json_decode($json, true));
    }
}
