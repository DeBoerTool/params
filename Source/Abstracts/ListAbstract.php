<?php

namespace Dbt\Params\Abstracts;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

abstract class ListAbstract implements JsonSerializable, IteratorAggregate,
                                       ArrayAccess, Countable
{
    protected array $list = [];

    abstract public function __construct (array $items = []);

    /**
     * @return static
     */
    abstract public static function hydrate (array $items): self;

    public function all (): array
    {
        return $this->list;
    }

    public function has (int $index): bool
    {
        return isset($this->list[$index]);
    }

    public function unset (int $index): void
    {
        unset($this->list[$index]);

        $this->list = array_values($this->list);
    }

    /**
     * @return static
     */
    public function filter (Closure $cb): self
    {
        return new static(array_filter($this->list, $cb));
    }

    public function map (Closure $cb): array
    {
        return array_map($cb, $this->list);
    }

    /**
     * @param \Closure $cb
     * @param mixed $initial
     * @return mixed
     */
    public function reduce (Closure $cb, $initial)
    {
        return array_reduce($this->list, $cb, $initial);
    }

    public function jsonSerialize (): array
    {
        return array_values($this->list);
    }

    public function getIterator (): Traversable
    {
        return new ArrayIterator($this->list);
    }

    public function count (): int
    {
        return count($this->list);
    }

    /**
     * @param mixed $offset
     */
    protected function isInt ($offset): void
    {
        if (!is_int($offset)) {
            throw new InvalidArgumentException(
                'The offset must be an integer.'
            );
        }
    }
}
