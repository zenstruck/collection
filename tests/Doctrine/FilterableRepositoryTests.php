<?php

namespace Zenstruck\Collection\Tests\Doctrine;

use Zenstruck\Collection\Doctrine\Specification\Context;
use Zenstruck\Collection\Spec;
use Zenstruck\Collection\Specification\Nested;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait FilterableRepositoryTests
{
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
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
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
        );

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_like(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::like('value', 'value 2'));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_like_wildcard(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::like('value', 'value *')->allowWildcard());

        $this->assertCount(3, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 2', \iterator_to_array($objects)[1]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[2]->value);
    }

    /**
     * @test
     */
    public function filter_contains(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::contains('value', 'value'));

        $this->assertCount(3, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 2', \iterator_to_array($objects)[1]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[2]->value);
    }

    /**
     * @test
     */
    public function filter_begins_with(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::beginsWith('value', 'v'));

        $this->assertCount(3, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 2', \iterator_to_array($objects)[1]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[2]->value);
    }

    /**
     * @test
     */
    public function filter_ends_with(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::endsWith('value', '2'));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_not_like(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notLike('value', 'value 2'));

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_not_like_wildcard(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notLike('value', 'value *')->allowWildcard());

        $this->assertEmpty($objects);
    }

    /**
     * @test
     */
    public function filter_not_contains(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notContains('value', 'value'));

        $this->assertEmpty($objects);
    }

    /**
     * @test
     */
    public function filter_not_beginning_with(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notBeginningWith('value', 'value'));

        $this->assertEmpty($objects);
    }

    /**
     * @test
     */
    public function filter_not_ends_with(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notEndingWith('value', '2'));

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_equal(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::eq('value', 'value 2'));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_not_equal(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::neq('value', 'value 2'));

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

        $this->assertEmpty($objects);
    }

    /**
     * @test
     */
    public function filter_is_not_null(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::isNotNull('value'));

        $this->assertCount(3, $objects);
    }

    /**
     * @test
     */
    public function filter_in_string(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::in('value', ['value 1', 'value 3']));

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_in_int(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::in('id', [1, 3]));

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_in_numeric_string(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::in('id', ['1', '3']));

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_in_mixed_str_field(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::in('value', ['1', 'value 2', 3]));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_in_mixed_int_field(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::in('id', ['1', 'value 2', 3]));

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_not_in_string(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notIn('value', ['value 1', 'value 3']));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_not_in_int(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notIn('id', [1, 3]));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_not_in_numeric_string(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notIn('id', ['1', '3']));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_not_in_mixed_str_field(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notIn('value', ['1', 'value 2', 3]));

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_not_in_mixed_int_field(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::notIn('id', ['1', 'value 2', 3]));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function filter_less_than(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::lt('id', 3));

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 2', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_less_than_equal(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::lte('id', 2));

        $this->assertCount(2, $objects);
        $this->assertSame('value 1', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 2', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_greater_than(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::gt('id', 1));

        $this->assertCount(2, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_greater_than_equal(): void
    {
        $objects = $this->createWithItems(3)->filter(Spec::gte('id', 2));

        $this->assertCount(2, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
        $this->assertSame('value 3', \iterator_to_array($objects)[1]->value);
    }

    /**
     * @test
     */
    public function filter_sort_desc(): void
    {
        $objects = \iterator_to_array($this->createWithItems(3)->filter(Spec::sortDesc('value')));

        $this->assertSame('value 3', $objects[0]->value);
        $this->assertSame('value 2', $objects[1]->value);
        $this->assertSame('value 1', $objects[2]->value);
    }

    /**
     * @test
     */
    public function filter_sort_asc(): void
    {
        $objects = \iterator_to_array($this->createWithItems(3)->filter(Spec::sortAsc('value')));

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
        ));

        $this->assertCount(2, $objects);
        $this->assertSame('value 3', $objects[0]->value);
        $this->assertSame('value 2', $objects[1]->value);
    }

    /**
     * @test
     */
    public function filter_one_for_single_comparison(): void
    {
        $object = $this->createWithItems(3)->get(Spec::eq('value', 'value 2'));

        $this->assertSame('value 2', $object->value);
    }

    /**
     * @test
     */
    public function not_found_exception_found_for_filter_one_if_no_result(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->createWithItems(3)->get(Spec::eq('value', 'value 6'));
    }

    /**
     * @test
     */
    public function can_use_nested_specification(): void
    {
        $object = $this->createWithItems(3)->get(new class() implements Nested {
            public function child(): mixed
            {
                return Spec::eq('value', 'value 2');
            }
        });

        $this->assertSame('value 2', $object->value);
    }

    /**
     * @test
     */
    public function can_filter_for_callback(): void
    {
        $object = $this->createWithItems(3)->get(function(Context $context) {
            $context->qb()->where($context->prefixAlias("value = 'value 2'"));
        });

        $this->assertSame('value 2', $object->value);
    }

    /**
     * @test
     */
    public function can_filter_for_callable_class(): void
    {
        $object = $this->createWithItems(3)->get(new class() {
            public function __invoke(Context $context): void
            {
                $context->qb()->where($context->prefixAlias("value = 'value 2'"));
            }
        });

        $this->assertSame('value 2', $object->value);
    }
}
