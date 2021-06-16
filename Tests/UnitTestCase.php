<?php

namespace Dbt\Params\Tests;

use Dbt\Params\Field;
use Dbt\Params\Param;
use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    public static function rs (int $chars): string
    {
        $string = '';

        while (($len = strlen($string)) < $chars) {
            $size = $chars - $len;

            $bytes = random_bytes($size);

            $string .= substr(
                str_replace(['/', '+', '='], '', base64_encode($bytes)),
                0,
                $size
            );
        }

        return $string;
    }

    public static function ri (int $min, int $max): int
    {
        return rand($min, $max);
    }

    protected function makeParam (array $overrides = []): Param
    {
        $values = array_merge([
            'uuid' => $this->rs(32),
            'join_uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'fields' => [],
        ], $overrides);

        return Param::hydrate($values);
    }

    protected function makeField (array $overrides = []): Field
    {
        $values = array_merge([
            'uuid' => $this->rs(32),
            'join_uuid' => $this->rs(32),
            'name' => $this->rs(16),
            'type' => $this->rs(16),
            'value' => rand(0, 99),
            'arguments' => [
                'arg1' => $this->rs(8),
            ]
        ], $overrides);

        return Field::hydrate($values);
    }
}
