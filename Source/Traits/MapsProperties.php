<?php

namespace Dbt\Params\Traits;

trait MapsProperties
{
    protected function mapProperties (string ...$props): array
    {
        $assoc = [];

        foreach ($props as $prop) {
            $assoc[$prop] = $this->{$prop};
        }

        return $assoc;
    }
}
