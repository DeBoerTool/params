<?php

namespace Dbt\Params;

use JsonSerializable;

class Param implements JsonSerializable
{
    private string $uuid;
    private string $joinUuid;
    private string $name;
    private string $type;
    private FieldMap $fields;

    public function __construct (
        string $uuid,
        string $joinUuid,
        string $name,
        string $type,
        FieldMap $fields
    )
    {
        $this->uuid = $uuid;
        $this->joinUuid = $joinUuid;
        $this->name = $name;
        $this->type = $type;
        $this->fields = $fields;
    }

    public static function hydrate (array $param): self
    {
        return new self(
            $param['uuid'],
            $param['join_uuid'],
            $param['name'],
            $param['type'],
            FieldMap::hydrate($param['fields'])
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

    public function fields (): FieldMap
    {
        return $this->fields;
    }

    public function toArray (): array
    {
        return [
            'uuid' => $this->uuid(),
            'join_uuid' => $this->joinUuid(),
            'name' => $this->name(),
            'type' => $this->type(),
            'fields' => $this->fields()->toArray(),
        ];
    }

    public function jsonSerialize (): array
    {
        return [
            'uuid' => $this->uuid(),
            'join_uuid' => $this->joinUuid(),
            'name' => $this->name(),
            'type' => $this->type(),
            'fields' => $this->fields(),
        ];
    }
}
