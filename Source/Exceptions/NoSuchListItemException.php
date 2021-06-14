<?php

namespace Dbt\Params\Exceptions;

use Exception;

class NoSuchListItemException extends Exception
{
    protected static string $format = 'No %s matching the given criteria was found.';

    public static function field (): self
    {
        return new self(sprintf(self::$format, 'Field'));
    }

    public static function param (): self
    {
        return new self(sprintf(self::$format, 'Param'));
    }
}
