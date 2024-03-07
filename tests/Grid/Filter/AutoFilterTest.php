<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Grid\Filter;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Grid\Filter\AutoFilter;
use Zenstruck\Collection\Specification\Filter\Between;
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
use Zenstruck\Collection\Specification\Logic\Not;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AutoFilterTest extends TestCase
{
    /**
     * @test
     * @dataProvider applyProvider
     */
    public function apply(mixed $value, ?object $expected): void
    {
        $this->assertEquals($expected, (new AutoFilter('foo'))->apply($value));
    }

    public static function applyProvider(): iterable
    {
        yield ['bar', new EqualTo('foo', 'bar')];
        yield ['*ba*r', new StartsWith('foo', 'ba*r')];
        yield ['ba*r*', new EndsWith('foo', 'ba*r')];
        yield ['*ba*r*', new Contains('foo', 'ba*r')];
        yield ['<=bar', new LessThanOrEqualTo('foo', 'bar')];
        yield ['<bar', new LessThan('foo', 'bar')];
        yield ['>=bar', new GreaterThanOrEqualTo('foo', 'bar')];
        yield ['>bar', new GreaterThan('foo', 'bar')];
        yield ['!bar', new Not(new EqualTo('foo', 'bar'))];
        yield ['bar,baz,qux', new In('foo', ['bar', 'baz', 'qux'])];
        yield ['!bar,baz,qux', new Not(new In('foo', ['bar', 'baz', 'qux']))];
        yield ['bar...baz', new Between('foo', 'bar', 'baz')];
        yield ['(bar...baz)', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE)];
        yield ['[bar...baz', new Between('foo', 'bar', 'baz', Between::INCLUSIVE)];
        yield ['bar...baz]', new Between('foo', 'bar', 'baz', Between::INCLUSIVE)];
        yield ['(bar...baz', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE_BEGIN)];
        yield ['bar...baz)', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE_END)];
        yield ['(bar...baz]', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE_BEGIN)];
        yield ['[bar...baz)', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE_END)];
        yield ['~', new IsNull('foo')];
        yield ['!~', new Not(new IsNull('foo'))];
        yield ['', null];
        yield [null, null];
        yield [['array'], null];
    }
}
