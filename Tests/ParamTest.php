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
        $array = [
            'uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'fields' => [
                [
                    'uuid' => $this->rs(32),
                    'name' => $this->rs(16),
                    'type' => $this->rs(16),
                    'value' => rand(1, 999),
                ]
            ]
        ];

        $param = Param::hydrate($array);

        $this->assertSame($array['uuid'], $param->uuid());
        $this->assertSame($array['name'], $param->name());
        $this->assertSame($array['type'], $param->type());
        $this->assertSame(
            $array['fields'][0],
            $param->fields()->get(0)->jsonSerialize(),
        );
    }

    /** @test */
    public function serializing_to_json (): void
    {
        $array = [
            'uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'fields' => [
                [
                    'uuid' => $this->rs(32),
                    'name' => $this->rs(16),
                    'type' => $this->rs(16),
                    'value' => rand(1, 999),
                ]
            ]
        ];

        $param = Param::hydrate($array);

        $json = json_encode($param);

        $this->assertSame($array, json_decode($json, true));
    }
}
