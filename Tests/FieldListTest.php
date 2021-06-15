<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Dbt\Params\Tests;

use Dbt\Params\Exceptions\NoSuchListItemException;
use Dbt\Params\Field;
use Dbt\Params\FieldList;
use InvalidArgumentException;
use TypeError;

/**
 * @covers \Dbt\Params\FieldList
 * @covers \Dbt\Params\Abstracts\ListAbstract
 */
class FieldListTest extends UnitTestCase
{
    /** @test */
    public function hydrating (): void
    {
        $array = [
            [
                'uuid' => $this->rs(32),
                'name' => $this->rs(16),
                'type' => $this->rs(16),
                'value' => rand(1, 999),
                'arguments' => [],
            ],
        ];

        $list = FieldList::hydrate($array);

        $this->assertSame(json_encode($array), json_encode($list));
        $this->assertSame($array, $list->toArray());
    }

    /** @test */
    public function merging (): void
    {
        $list0 = new FieldList([
            new Field('', '', '', 0),
        ]);

        $list1 = new FieldList([
            new Field('', '', '', 1),
        ]);

        $merged = $list0->merge($list1);

        $this->assertSame(0, $merged[0]->value());
        $this->assertSame(1, $merged[1]->value());
    }

    /** @test */
    public function filtering (): void
    {
        $list = new FieldList([
            new Field('', '', '', 9.9),
            new Field('', '', '', 9.8),
            new Field('', '', '', 9.8),
            new Field('', '', '', 9.7),
        ]);

        $filtered = $list->filter(
            fn (Field $field): bool => $field->value() === 9.8
        );

        $this->assertNotSame($list, $filtered);
        foreach ($filtered as $field) {
            $this->assertSame(9.8, $field->value());
        }
    }

    /** @test */
    public function sorting (): void
    {
        $list = new FieldList([
            new Field('', '', '', 1),
            new Field('', '', '', 2),
        ]);

        $sorted = $list->sort(
            fn (Field $a, Field $b): int => $b->value() <=> $a->value(),
        );

        $this->assertNotSame($list, $sorted);
        $this->assertSame(2, $sorted->get(0)->value());
        $this->assertSame(1, $sorted->get(1)->value());
    }

    /** @test */
    public function mapping (): void
    {
        $list = new FieldList([
            new Field('', '1', '', 9.9),
            new Field('', '2', '', 9.8),
            new Field('', '3', '', 9.7),
        ]);

        $mapped = $list->map(fn (Field $field): string => $field->name());

        $this->assertIsArray($mapped);
        $this->assertSame(['1', '2', '3'], $mapped);
    }

    /** @test */
    public function reducing (): void
    {
        $list = new FieldList([
            new Field('', '', '', 1),
            new Field('', '', '', 2),
            new Field('', '', '', 3),
        ]);

        $reduced = $list->reduce(
            fn (int $acc, Field $field): int => $acc + $field->value(),
            0,
        );

        $this->assertIsInt($reduced);
        $this->assertSame(6, $reduced);
    }

    /** @test */
    public function finding (): void
    {
        $list = new FieldList([
            new Field('', '', '', 1),
            new Field('', '', '', 2),
            new Field('', '', '', 3),
        ]);

        $found = $list->find(fn (Field $field): bool => $field->value() === 2);

        $this->assertSame(2, $found->value());
    }

    /** @test */
    public function not_finding (): void
    {
        $list = new FieldList();

        $this->expectException(NoSuchListItemException::class);

        $list->find(fn (Field $field): bool => $field->value() === 2);
    }

    /** @test */
    public function iterating (): void
    {
        $list = new FieldList([
            new Field('', '', '', 0),
            new Field('', '', '', 1),
        ]);

        foreach ($list as $key => $field) {
            $this->assertSame($key, $field->value());
        }
    }

    /** @test */
    public function offset_get (): void
    {
        $list = new FieldList([
            new Field('', '', '', 0),
            new Field('', '', '', 1),
        ]);

        $this->assertSame($list[1]->value(), 1);
    }

    /** @test */
    public function offset_exists (): void
    {
        $list = new FieldList([
            new Field('', '', '', 0),
            new Field('', '', '', 1),
        ]);

        $this->assertTrue(isset($list[1]));
        $this->assertFalse(isset($list[2]));
    }

    /** @test */
    public function offset_set (): void
    {
        $list = new FieldList([
            new Field('', '', '', 0),
            new Field('', '', '', 1),
        ]);

        /**
         * Attempting to set an index past the end of the list just pushes onto
         * the end of the list.
         */
        $list[3] = new Field('', '', '', 2);

        /**
         * However overwriting existing members is fine.
         */
        $list[0] = new Field('', '', '', 'x');

        $this->assertSame(2, $list[2]->value());
        $this->assertSame('x', $list[0]->value());
    }

    /** @test */
    public function offset_set_failure_non_integer_key (): void
    {
        $list = new FieldList();

        $this->expectException(InvalidArgumentException::class);

        $list['string-key'] = new Field('', '', '', 0);
    }

    /** @test */
    public function offset_set_failure_non_field_value (): void
    {
        $list = new FieldList();

        $this->expectException(TypeError::class);

        $list[0] = ['an array'];
    }

    /** @test */
    public function offset_unset (): void
    {
        $list = new FieldList([
            new Field('', '', '', 0),
            new Field('', '', '', 1),
        ]);

        unset($list[0]);

        /**
         * Unsetting a key reorders the list so no gaps exist.
         */
        $this->assertSame(1, $list[0]->value());
    }

    /** @test */
    public function offset_unset_failure_non_int_key (): void
    {
        $list = new FieldList();

        $this->expectException(InvalidArgumentException::class);

        unset($list['string-key']);
    }
}
