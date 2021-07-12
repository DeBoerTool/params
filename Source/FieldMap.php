<?php

namespace Dbt\Params;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use Dbt\Params\Exceptions\NotFoundException;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

class FieldMap implements IteratorAggregate, Countable, ArrayAccess,
                          JsonSerializable
{
    private array $items;

    /**
     * @param array<string, \Dbt\Params\Field> $items
     */
    public function __construct (array $items = [])
    {
        $this->items = [];

        foreach ($items as $item) {
            $this->put($item->joinUuid(), $item);
        }
    }

    public static function hydrate (array $items): self
    {
        $array = [];

        foreach ($items as $field) {
            $array[$field['join_uuid']] = Field::hydrate($field);
        }

        return new self($array);
    }

    public function get (string $joinUuid): Field
    {
        return $this->items[$joinUuid];
    }

    public function has (string $joinUuid): bool
    {
        return isset($this->items[$joinUuid]);
    }

    public function put (string $joinUuid, Field $field): self
    {
        $this->items[$joinUuid] = $field;

        return $this;
    }

    /**
     * @param bool|float|int|string|null $value
     */
    public function mutate (string $joinUuid, $value): self
    {
        $this->items[$joinUuid] = $this->items[$joinUuid]->mutate($value);

        return $this;
    }

    public function unset (string $joinUuid): void
    {
        unset($this->items[$joinUuid]);
    }

    public function filter (Closure $cb): FieldMap
    {
        $map = new self();

        foreach ($this->items as $joinUuid => $field) {
            if ($cb($joinUuid, $field)) {
                $map->put($joinUuid, $field);
            }
        }

        return $map;
    }

    public function items (Closure $cb): array
    {
        $mapped = [];

        foreach ($this->items as $joinUuid => $field) {
            $mapped[] = $cb($joinUuid, $field);
        }

        return $mapped;
    }

    public function mapWithKeys (Closure $cb): array
    {
        $mapped = [];

        foreach ($this->items as $joinUuid => $field) {
            $array = $cb($joinUuid, $field);

            $mapped[array_keys($array)[0]] = array_values($array)[0];
        }

        return $mapped;
    }

    /**
     * @throws \Dbt\Params\Exceptions\NotFoundException
     */
    public function find (Closure $cb): Field
    {
        foreach ($this->items as $joinUuid => $field) {
            if ($cb($joinUuid, $field)) {
                return $field;
            }
        }

        throw NotFoundException::field();
    }

    /**
     * @param \Closure $cb
     * @param mixed $initial
     * @return mixed
     */
    public function reduce (Closure $cb, $initial)
    {
        $reduced = $initial;

        foreach ($this->items as $joinUuid => $field) {
            $reduced = $cb($reduced, $joinUuid, $field);
        }

        return $reduced;
    }

    public function toArray (): array
    {
        $array = [];

        /**
         * @var string $joinUuid
         * @var Field $field
         */
        foreach ($this->items as $joinUuid => $field) {
            $array[$joinUuid] = $field->toArray();
        }

        return $array;
    }

    public function offsetExists ($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet ($offset): Field
    {
        return $this->get($offset);
    }

    public function offsetSet ($offset, $value): void
    {
        $this->put($offset, $value);
    }

    public function offsetUnset ($offset): void
    {
        $this->unset($offset);
    }

    public function getIterator (): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count (): int
    {
        return count($this->items);
    }

    public function jsonSerialize ()
    {
        return $this->items;
    }
}
