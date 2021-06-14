<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Dbt\Params\Tests;

use Dbt\Params\Exceptions\NoSuchListItemException;
use Dbt\Params\Field;
use Dbt\Params\FieldList;
use Dbt\Params\ParamList;
use Dbt\Params\Param;
use InvalidArgumentException;
use TypeError;

/**
 * @covers \Dbt\Params\ParamList
 * @covers \Dbt\Params\Abstracts\ListAbstract
 */
class ParamListTest extends UnitTestCase
{
    /** @test */
    public function hydrating (): void
    {
        $array = [
            [
                'uuid' => $this->rs(32),
                'name' => $this->rs(16),
                'type' => $this->rs(16),
                'fields' => [],
            ],
        ];

        $list = ParamList::hydrate($array);

        $this->assertSame(json_encode($array), json_encode($list));
    }

    /** @test */
    public function collapsing_to_a_single_field_list (): void
    {
        $field0 = new Field('', '', '', 0);
        $field1 = new Field('', '', '', 1);

        $list = new ParamList([
            new Param('', '1', '', new FieldList([$field0])),
            new Param('', '2', '', new FieldList([$field1])),
        ]);

        $collapsed = $list->collapse();

        $this->assertSame($collapsed[0], $field0);
        $this->assertSame($collapsed[1], $field1);
    }

    /** @test */
    public function filtering (): void
    {
        $list = new ParamList([
            new Param('', '1', '', new FieldList()),
            new Param('', '2', '', new FieldList()),
            new Param('', '3', '', new FieldList()),
            new Param('', '4', '', new FieldList([
                new Field('', '', '', 0)
            ])),
        ]);

        $filtered = $list->filter(
            fn (Param $param): bool => count($param->fields())
        );

        foreach ($filtered as $param) {
            $this->assertSame('4', $param->name());
        }
    }

    /** @test */
    public function mapping (): void
    {
        $list = new ParamList([
            new Param('', '1', '', new FieldList()),
            new Param('', '2', '', new FieldList()),
            new Param('', '3', '', new FieldList()),
        ]);

        $mapped = $list->map(fn (Param $param): string => $param->name());

        $this->assertIsArray($mapped);
        $this->assertSame(['1', '2', '3'], $mapped);
    }

    /** @test */
    public function reducing (): void
    {
        $list = new ParamList([
            new Param('', '', '', new FieldList([
                new Field('', '', '', 0)
            ])),
            new Param('', '', '', new FieldList([
                new Field('', '', '', 0),
                new Field('', '', '', 0)
            ])),
            new Param('', '', '', new FieldList([
                new Field('', '', '', 0),
                new Field('', '', '', 0),
                new Field('', '', '', 0),
            ])),
        ]);

        $reduced = $list->reduce(
            fn (int $acc, Param $param): int => $acc + count($param->fields()),
            0,
        );

        $this->assertIsInt($reduced);
        $this->assertSame(6, $reduced);
    }

    /** @test */
    public function finding (): void
    {
        $list = new ParamList([
            new Param('', '1', '', new FieldList()),
            new Param('', '2', '', new FieldList()),
            new Param('', '3', '', new FieldList()),
        ]);

        $found = $list->find(fn (Param $param): bool => $param->name() === '2');

        $this->assertSame('2', $found->name());
    }

    /** @test */
    public function not_finding (): void
    {
        $list = new ParamList();

        $this->expectException(NoSuchListItemException::class);

        $list->find(fn (Param $field): bool => $field->name() === '2');
    }

    /** @test */
    public function getting_an_index (): void
    {
        $list = new ParamList([
            new Param('', '', '0', new FieldList()),
            new Param('', '', '1', new FieldList()),
        ]);

        $this->assertSame($list->get(1)->type(), '1');
    }

    /** @test */
    public function iterating (): void
    {
        $list = new ParamList([
            new Param('', '', '0', new FieldList()),
            new Param('', '', '1', new FieldList()),
        ]);

        foreach ($list as $key => $field) {
            $this->assertSame((string) $key, $field->type());
        }
    }

    /** @test */
    public function offset_get (): void
    {
        $list = new ParamList([
            new Param('', '', '0', new FieldList()),
            new Param('', '', '1', new FieldList()),
        ]);

        $this->assertSame('1', $list[1]->type());
    }

    /** @test */
    public function offset_exists (): void
    {
        $list = new ParamList([
            new Param('', '', '0', new FieldList()),
            new Param('', '', '1', new FieldList()),
        ]);

        $this->assertTrue(isset($list[1]));
        $this->assertFalse(isset($list[2]));
    }

    /** @test */
    public function offset_set (): void
    {
        $list = new ParamList([
            new Param('', '', '0', new FieldList()),
            new Param('', '', '1', new FieldList()),
        ]);

        /**
         * Attempting to set an index past the end of the list just pushes onto
         * the end of the list.
         */
        $list[3] = new Param('', '', '2', new FieldList());

        /**
         * However overwriting existing members is fine.
         */
        $list[0] = new Param('', '', 'x', new FieldList());

        $this->assertSame('2', $list[2]->type());
        $this->assertSame('x', $list[0]->type());
    }

    /** @test */
    public function offset_set_failure_non_integer_key (): void
    {
        $list = new ParamList();

        $this->expectException(InvalidArgumentException::class);

        $list['string-key'] = new Param('', '', 'x', new FieldList());
    }

    /** @test */
    public function offset_set_failure_non_field_value (): void
    {
        $list = new ParamList();

        $this->expectException(TypeError::class);

        $list[0] = ['an array'];
    }

    /** @test */
    public function offset_unset (): void
    {
        $list = new ParamList([
            new Param('', '', '0', new FieldList()),
            new Param('', '', '1', new FieldList()),
        ]);

        unset($list[0]);

        /**
         * Unsetting a key reorders the list so no gaps exist.
         */
        $this->assertSame('1', $list[0]->type());
    }

    /** @test */
    public function offset_unset_failure_non_int_key (): void
    {
        $list = new ParamList();

        $this->expectException(InvalidArgumentException::class);

        unset($list['string-key']);
    }
}
