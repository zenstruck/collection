<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection;

use Zenstruck\Collection\Specification\Callback;
use Zenstruck\Collection\Specification\Filter\Contains;
use Zenstruck\Collection\Specification\Filter\EndsWith;
use Zenstruck\Collection\Specification\Filter\EqualTo;
use Zenstruck\Collection\Specification\Filter\GreaterThan;
use Zenstruck\Collection\Specification\Filter\GreaterThanOrEqualTo;
use Zenstruck\Collection\Specification\Filter\In;
use Zenstruck\Collection\Specification\Filter\IsNull;
use Zenstruck\Collection\Specification\Filter\LessThan;
use Zenstruck\Collection\Specification\Filter\LessThanOrEqualTo;
use Zenstruck\Collection\Specification\Filter\StartsWith;
use Zenstruck\Collection\Specification\Logic\AndX;
use Zenstruck\Collection\Specification\Logic\Not;
use Zenstruck\Collection\Specification\Logic\OrX;
use Zenstruck\Collection\Specification\OrderBy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Spec
{
    private function __construct()
    {
    }

    final public static function andX(mixed ...$children): AndX
    {
        return new AndX(...$children);
    }

    final public static function orX(mixed ...$children): OrX
    {
        return new OrX(...$children);
    }

    final public static function not(mixed $restriction): Not
    {
        return new Not($restriction);
    }

    final public static function eq(string $field, mixed $value): EqualTo
    {
        return new EqualTo($field, $value);
    }

    final public static function contains(string $field, string $value): Contains
    {
        return new Contains($field, $value);
    }

    final public static function startsWith(string $field, string $value): StartsWith
    {
        return new StartsWith($field, $value);
    }

    final public static function endsWith(string $field, ?string $value): EndsWith
    {
        return new EndsWith($field, $value);
    }

    final public static function isNull(string $field): IsNull
    {
        return new IsNull($field);
    }

    /**
     * @param mixed[] $values
     */
    final public static function in(string $field, array $values): In
    {
        return new In($field, $values);
    }

    final public static function lt(string $field, mixed $value): LessThan
    {
        return new LessThan($field, $value);
    }

    final public static function lte(string $field, mixed $value): LessThanOrEqualTo
    {
        return new LessThanOrEqualTo($field, $value);
    }

    final public static function gt(string $field, mixed $value): GreaterThan
    {
        return new GreaterThan($field, $value);
    }

    final public static function gte(string $field, mixed $value): GreaterThanOrEqualTo
    {
        return new GreaterThanOrEqualTo($field, $value);
    }

    /**
     * @template C
     *
     * @param callable(C):mixed $value
     *
     * @return Callback<C>
     */
    final public static function callback(callable $value): Callback
    {
        return new Callback($value);
    }

    final public static function sortAsc(string $field): OrderBy
    {
        return OrderBy::asc($field);
    }

    final public static function sortDesc(string $field): OrderBy
    {
        return OrderBy::desc($field);
    }
}
