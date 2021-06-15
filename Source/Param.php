<?php

namespace Dbt\Params;

use Dbt\Params\Traits\MapsProperties;
use JsonSerializable;

class Param implements JsonSerializable
{
    use MapsProperties;

    private string $uuid;
    private string $name;
    private string $type;
    private FieldList $fields;

    public function __construct (
        string $uuid,
        string $name,
        string $type,
        FieldList $fields
    )
    {
        $this->uuid = $uuid;
        $this->name = $name;
        $this->type = $type;
        $this->fields = $fields;
    }

    public static function hydrate (array $param): self
    {
        return new self(
            $param['uuid'],
            $param['name'],
            $param['type'],
            FieldList::hydrate($param['fields'])
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

    public function fields (): FieldList
    {
        return $this->fields;
    }

    public function toArray (): array
    {
        return array_merge(
            $this->mapProperties('uuid', 'name', 'type'),
            ['fields' => $this->fields->toArray()]
        );
    }

    public function jsonSerialize (): array
    {
        return $this->mapProperties('uuid', 'name', 'type', 'fields');
    }
}
