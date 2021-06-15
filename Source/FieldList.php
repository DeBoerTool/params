<?php

namespace Dbt\Params;

use Closure;
use Dbt\Params\Abstracts\ListAbstract;
use Dbt\Params\Exceptions\NoSuchListItemException;

class FieldList extends ListAbstract
{
    /**
     * @param Field[] $items
     */
    public function __construct (array $items = [])
    {
        foreach ($items as $item) {
            $this->push($item);
        }
    }

    public static function hydrate (array $items): self
    {
        return new self(array_map(
            fn (array $field): Field => Field::hydrate($field),
            $items
        ));
    }

    public function get (int $index): Field
    {
        return $this->list[$index];
    }

    public function push (Field $field): self
    {
        $this->list[] = $field;

        return $this;
    }

    public function merge (self $toMerge): self
    {
        return new self(array_merge(
            $this->list,
            $toMerge->all()
        ));
    }

    public function set (int $index, Field $field): void
    {
        if (!isset($this->list[$index])) {
            $index = count($this->list);
        }

        $this->list[$index] = $field;
    }

    /**
     * @throws \Dbt\Params\Exceptions\NoSuchListItemException
     */
    public function find (Closure $cb): Field
    {
        foreach ($this->list as $field) {
            if ($cb($field)) {
                return $field;
            }
        }

        throw new NoSuchListItemException();
    }

    public function toArray (): array
    {
        return array_map(
            fn (Field $field): array => $field->toArray(),
            $this->list
        );
    }

    /**
     * @param int $offset
     */
    public function offsetExists ($offset): bool
    {
        $this->isInt($offset);

        return $this->has($offset);
    }

    /**
     * @param int $offset
     */
    public function offsetGet ($offset): Field
    {
        $this->isInt($offset);

        return $this->get($offset);
    }

    /**
     * @param int $offset
     * @param \Dbt\Params\Field $value
     */
    public function offsetSet ($offset, $value): void
    {
        $this->isInt($offset);

        $this->set($offset, $value);
    }

    /**
     * @param int $offset
     */
    public function offsetUnset ($offset): void
    {
        $this->isInt($offset);

        $this->unset($offset);
    }
}
