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

class ParamMap implements IteratorAggregate, Countable, ArrayAccess,
                          JsonSerializable
{
    private array $items;

    /**
     * @param array<string, Param> $items
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

        foreach ($items as $param) {
            $array[$param['join_uuid']] = Param::hydrate($param);
        }

        return new self($array);
    }

    public function get (string $joinUuid): Param
    {
        return $this->items[$joinUuid];
    }

    public function has (string $joinUuid): bool
    {
        return isset($this->items[$joinUuid]);
    }

    public function put (string $joinUuid, Param $param): self
    {
        $this->items[$joinUuid] = $param;

        return $this;
    }

    public function unset (string $joinUuid): void
    {
        unset($this->items[$joinUuid]);
    }

    public function filter (Closure $cb): ParamMap
    {
        $map = new self();

        foreach ($this->items as $joinUuid => $param) {
            if ($cb($joinUuid, $param)) {
                $map->put($joinUuid, $param);
            }
        }

        return $map;
    }

    public function items (Closure $cb): array
    {
        $mapped = [];

        foreach ($this->items as $joinUuid => $param) {
            $mapped[] = $cb($joinUuid, $param);
        }

        return $mapped;
    }

    public function mapWithKeys (Closure $cb): array
    {
        $mapped = [];

        foreach ($this->items as $joinUuid => $param) {
            $array = $cb($joinUuid, $param);

            $mapped[array_keys($array)[0]] = array_values($array)[0];
        }

        return $mapped;
    }

    /**
     * @throws \Dbt\Params\Exceptions\NotFoundException
     */
    public function find (Closure $cb): Param
    {
        foreach ($this->items as $joinUuid => $param) {
            if ($cb($joinUuid, $param)) {
                return $param;
            }
        }

        throw NotFoundException::param();
    }

    /**
     * Get the value of the first matching Param and Field.
     * @throws \Dbt\Params\Exceptions\NotFoundException
     */
    public function pluckByName (string $paramName, string $fieldName)
    {
        $param = $this->find(
            fn($_, Param $param) => $param->name() === $paramName
        );

        $field = $param->fields()->find(
            fn($_, Field $field) => $field->name() === $fieldName
        );

        return $field->value();
    }

    public function values (): array
    {
        $values = [];

        /**
         * @var \Dbt\Params\Param $param
         */
        foreach ($this->items as $param) {
            /**
             * @var \Dbt\Params\Field $field
             */
            foreach ($param->fields() as $field) {
                $key = implode('_', [
                    $param->compositeKey(),
                    $field->compositeKey(),
                ]);

                $values[$key] = $field->value();
            }
        }

        return $values;
    }

    /**
     * @param \Closure $cb
     * @param mixed $initial
     * @return mixed
     */
    public function reduce (Closure $cb, $initial)
    {
        $reduced = $initial;

        foreach ($this->items as $joinUuid => $param) {
            $reduced = $cb($reduced, $joinUuid, $param);
        }

        return $reduced;
    }

    /**
     * @return Field[]
     */
    public function fieldList (): array
    {
        $fields = [];

        foreach ($this->items as $param) {
            foreach ($param->fields() as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    public function toArray (): array
    {
        $array = [];

        /**
         * @var string $joinUuid
         * @var Param $param
         */
        foreach ($this->items as $joinUuid => $param) {
            $array[$joinUuid] = $param->toArray();
        }

        return $array;
    }

    public function offsetExists ($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet ($offset): Param
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
