<?php

namespace Dbt\Params;

use Closure;
use Dbt\Params\Abstracts\ListAbstract;
use Dbt\Params\Exceptions\NoSuchListItemException;

class ParamList extends ListAbstract
{
    /**
     * @param Param[] $items
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
            fn (array $param): Param => Param::hydrate($param),
            $items
        ));
    }

    public function collapse (): FieldList
    {
        return $this->reduce(
            fn (FieldList $acc, Param $param): FieldList => $acc->merge(
                $param->fields()
            ),
            new FieldList()
        );
    }

    public function get (int $index): Param
    {
        return $this->list[$index];
    }

    public function push (Param $param): self
    {
        $this->list[] = $param;

        return $this;
    }

    public function set (int $index, Param $field): void
    {
        if (!isset($this->list[$index])) {
            $index = count($this->list);
        }

        $this->list[$index] = $field;
    }

    /**
     * @throws \Dbt\Params\Exceptions\NoSuchListItemException
     */
    public function find (Closure $cb): Param
    {
        foreach ($this->list as $param) {
            if ($cb($param)) {
                return $param;
            }
        }

        throw NoSuchListItemException::param();
    }

    public function offsetExists ($offset): bool
    {
        $this->isInt($offset);

        return $this->has($offset);
    }

    public function offsetGet ($offset): Param
    {
        $this->isInt($offset);

        return $this->get($offset);
    }

    public function offsetSet ($offset, $value): void
    {
        $this->isInt($offset);

        $this->set($offset, $value);
    }

    public function offsetUnset ($offset): void
    {
        $this->isInt($offset);

        $this->unset($offset);
    }
}
