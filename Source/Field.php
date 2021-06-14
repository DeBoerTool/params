<?php

namespace Dbt\Params;

use Dbt\Params\Traits\MapsProperties;
use InvalidArgumentException;
use JsonSerializable;

class Field implements JsonSerializable
{
    use MapsProperties;

    private string $uuid;
    private string $name;
    private string $type;

    /** @var string|int|float|bool */
    private $value;

    /**
     * @param string|int|float|bool|mixed $value
     */
    public function __construct (
        string $uuid,
        string $name,
        string $type,
        $value
    )
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->type = $type;

        if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
            throw new InvalidArgumentException('Invalid value type.');
        }

        $this->value = $value;
    }

    public static function hydrate (array $field): self
    {
        return new self(
            $field['uuid'],
            $field['name'],
            $field['type'],
            $field['value'],
        );
    }

    public function uuid (): string
    {
        return $this->uuid;
    }

    public function name (): string
    {
        return $this->name;
    }

    public function type (): string
    {
        return $this->type;
    }

    /**
     * @return bool|float|int|string
     */
    public function value ()
    {
        return $this->value;
    }

    public function toArray (): array
    {
        return $this->mapProperties('uuid', 'name', 'type', 'value');
    }

    public function jsonSerialize (): array
    {
        return $this->toArray();
    }
}