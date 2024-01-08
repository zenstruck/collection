<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests;

use Zenstruck\Collection\Matchable;
use Zenstruck\Collection\Spec;
use Zenstruck\Collection\Specification\Nested;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait MatchableObjectTests
{
    /**
     * @test
     */
    public function can_use_specification_object_for_filter(): void
    {
        $collection = $this->createWithItems(10);
        $spec = Spec::andX(
            Spec::lt('id', 5),
            Spec::sortDesc('id'),
        );

        $this->assertEquals($this->expectedValueAt(4), $collection->filter($spec)->first());
    }

    /**
     * @test
     */
    public function can_use_specification_object_for_find(): void
    {
        $collection = $this->createWithItems(10);
        $spec = Spec::andX(
            Spec::lt('id', 5),
            Spec::sortDesc('id'),
        );

        $this->assertEquals($this->expectedValueAt(4), $collection->find($spec));
    }

    /**
     * @test
     */
    public function filter_and_x_composite(): void
    {
        $repo = $this->createWithItems(3);

        $objects = $repo->filter(
            Spec::andX(
                Spec::gt('id', 1),
                Spec::lt('id', 3)
            )
        );

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', $objects->first()->value);
    }

    /**
     * @test
     */
    public function filter_or_x_composite(): void
    {
        $objects = $this->createWithItems(3)->filter(
            Spec::orX(
                Spec::lt('id', 2),
                Spec::gt('id', 2)
            )
        )->eager()->values();

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_contains_exact(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::contains('value', 'value 2'))->eager()->values();

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_contains_partial(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::contains('value', 'lue'))->eager()->values();

        $this->assertCount(3, $objects);
    }

    /**
     * @test
     */
    public function filter_contains_no_match(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::contains('value', 'invalid'));

        $this->assertCount(0, $objects);
    }

    /**
     * @test
     */
    public function filter_starts_with(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::startsWith('value', 'va'))->eager()->values();

        $this->assertCount(3, $objects);
    }

    /**
     * @test
     */
    public function filter_starts_with_no_match(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::startsWith('value', 'invalid'));

        $this->assertCount(0, $objects);
    }

    /**
     * @test
     */
    public function filter_ends_with(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::endsWith('value', '2'))->eager()->values();

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_ends_with_no_match(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::endsWith('value', 'invalid'));

        $this->assertCount(0, $objects);
    }

    /**
     * @test
     */
    public function filter_not_contains(): void
    {
        $objects = $this->createWithItems(3)
            ->filter(Spec::not(Spec::contains('value', 'value 2')))
            ->eager()
            ->values()
        ;

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_is_null(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::isNull('value'));

        $this->assertCount(0, $objects);
    }

    /**
     * @test
     */
    public function filter_is_not_null(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::not(Spec::isNull('value')));

        $this->assertCount(3, $objects);
    }

    /**
     * @test
     */
    public function filter_in_string(): void
    {
        $objects = $this->createWithItems(3)
            ->filter(Spec::in('value', ['value 1', 'value 3']))
            ->eager()
            ->values()
        ;

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_less_than(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::lt('id', 3))->eager()->values();

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 2', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_less_than_equal(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::lte('id', 2))->eager()->values();

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 2', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_greater_than(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::gt('id', 1))->eager()->values();

        $this->assertCount(2, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_greater_than_equal(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::gte('id', 2))->eager()->values();

        $this->assertCount(2, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_sort_desc(): void
    {
        $objects = \iterator_to_array(
            $this->createWithItems(3)->filter(Spec::sortDesc('value'))->eager()->values()
        );

        $this->assertSame('value 3', $objects[0]->value);
        $this->assertSame('value 2', $objects[1]->value);
        $this->assertSame('value 1', $objects[2]->value);
    }

    /**
     * @test
     */
    public function filter_sort_asc(): void
    {
        $objects = \iterator_to_array(
            $this->createWithItems(3)->filter(Spec::sortAsc('value'))->eager()->values()
        );

        $this->assertSame('value 1', $objects[0]->value);
        $this->assertSame('value 2', $objects[1]->value);
        $this->assertSame('value 3', $objects[2]->value);
    }

    /**
     * @test
     */
    public function filter_composite_order_by(): void
    {
        $objects = \iterator_to_array($this->createWithItems(3)->filter(
            Spec::andX(
                Spec::gt('id', 1),
                Spec::sortDesc('id')
            )
        )->eager()->values());

        $this->assertCount(2, $objects);
        $this->assertSame('value 3', $objects[0]->value);
        $this->assertSame('value 2', $objects[1]->value);
    }

    /**
     * @test
     */
    public function can_use_nested_specification(): void
    {
        $object = $this->createWithItems(3)->find(new class() implements Nested {
            public function specification(): mixed
            {
                return Spec::eq('value', 'value 2');
            }
        });

        $this->assertSame('value 2', $object->value);
    }

    abstract protected function createWithItems(int $count): Matchable;
}
