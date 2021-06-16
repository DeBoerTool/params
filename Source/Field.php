<?php

namespace Dbt\Params;

use InvalidArgumentException;
use JsonSerializable;

class Field implements JsonSerializable
{
    private string $uuid;
    private string $joinUuid;
    private string $name;
    private string $type;
    private array $arguments;

    /** @var string|int|float|bool|null */
    private $value;

    /**
     * @param string|int|float|bool|null $value
     */
    public function __construct (
        string $uuid,
        string $joinUuid,
        string $name,
        string $type,
        $value,
        array $arguments = []
    )
    {
        $this->uuid = $uuid;
        $this->joinUuid = $joinUuid;
        $this->name = $name;
        $this->type = $type;
        $this->arguments = $arguments;

        if (!$this->isValidType($value)) {
            throw new InvalidArgumentException('Invalid value type.');
        }

        $this->value = $value;
    }

    public static function hydrate (array $field): self
    {
        return new self(
            $field['uuid'],
            $field['join_uuid'],
            $field['name'],
            $field['type'],
            $field['value'] ?? null,
            $field['arguments'] ?? [],
        );
    }

    public function uuid (): string
    {
        return $this->uuid;
    }

    public function joinUuid (): string
    {
        return $this->joinUuid;
    }

    public function name (): string
    {
        return $this->name;
    }

    public function type (): string
    {
        return $this->type;
    }

    public function arguments (): array
    {
        return $this->arguments;
    }

    /**
     * @return bool|float|int|string|null
     */
    public function value ()
    {
        return $this->value;
    }

    public function compositeKey (): string
    {
        return implode('_', [$this->joinUuid(), $this->uuid()]);
    }

    public function toArray (): array
    {
        return [
            'uuid' => $this->uuid,
            'join_uuid' => $this->joinUuid,
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'arguments' => $this->arguments,
        ];
    }

    public function jsonSerialize (): array
    {
        return $this->toArray();
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function isValidType ($value): bool
    {
        return is_string($value)
            || is_int($value)
            || is_float($value)
            || is_bool($value)
            || is_null($value);
    }
}
