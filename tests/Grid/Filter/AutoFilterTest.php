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
use Zenstruck\Collection\Grid\Input\UriInput;
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

    /**
     * @test
     */
    public function supports_maximum_negation_depth(): void
    {
        $value = 'bar';
        $expected = new EqualTo('foo', $value);

        for ($depth = 0; $depth < 32; ++$depth) {
            $value = '!'.$value;
            $expected = new Not($expected);
        }

        $this->assertEquals($expected, (new AutoFilter('foo'))->apply($value));
    }

    /**
     * @test
     */
    public function consumes_negation_as_ascii(): void
    {
        $encoding = \mb_internal_encoding();
        \mb_internal_encoding('UTF-16LE');

        try {
            $specification = (new AutoFilter('foo'))->apply('!x!x!x');
        } finally {
            \mb_internal_encoding($encoding);
        }

        $this->assertEquals(new Not(new EqualTo('foo', 'x!x!x')), $specification);
    }

    /**
     * @test
     * @dataProvider excessiveNegationProvider
     */
    public function rejects_excessive_negation(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot negate a filter more than 32 times.');

        (new AutoFilter('foo'))->apply($value);
    }

    /**
     * @test
     */
    public function rejects_excessive_negation_from_uri_input(): void
    {
        $input = new UriInput('?filters[foo]='.\str_repeat('%21', 33).'bar');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot negate a filter more than 32 times.');

        (new AutoFilter('foo'))->apply($input->filter('foo'));
    }

    public static function excessiveNegationProvider(): iterable
    {
        yield [\str_repeat('!', 33).'bar'];
        yield [\str_repeat('!', 8192).'bar'];
        yield [\str_repeat('!', 8193).'~'];
        yield [\str_repeat('!', 8192)];
        yield [\str_repeat('!', 33).'0'];
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
        yield ['!!bar', new Not(new Not(new EqualTo('foo', 'bar')))];
        yield ['!>10', new Not(new GreaterThan('foo', '10'))];
        yield ['!<=10', new Not(new LessThanOrEqualTo('foo', '10'))];
        yield ['!*bar', new Not(new StartsWith('foo', 'bar'))];
        yield ['!bar*', new EndsWith('foo', '!bar')];
        yield ['!*bar*', new EndsWith('foo', '!*bar')];
        yield ['bar,baz,qux', new In('foo', ['bar', 'baz', 'qux'])];
        yield ['!bar,baz,qux', new Not(new In('foo', ['bar', 'baz', 'qux']))];
        yield ['bar...baz', new Between('foo', 'bar', 'baz')];
        yield ['!bar...baz', new Between('foo', '!bar', 'baz')];
        yield [\str_repeat('!', 8192).'bar...baz', new Between('foo', \str_repeat('!', 8192).'bar', 'baz')];
        yield [\str_repeat('!', 8192).'bar*', new EndsWith('foo', \str_repeat('!', 8192).'bar')];
        yield ['(bar...baz)', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE)];
        yield ['[bar...baz', new Between('foo', 'bar', 'baz', Between::INCLUSIVE)];
        yield ['bar...baz]', new Between('foo', 'bar', 'baz', Between::INCLUSIVE)];
        yield ['(bar...baz', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE_BEGIN)];
        yield ['bar...baz)', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE_END)];
        yield ['(bar...baz]', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE_BEGIN)];
        yield ['[bar...baz)', new Between('foo', 'bar', 'baz', Between::EXCLUSIVE_END)];
        yield ['~', new IsNull('foo')];
        yield ['!~', new Not(new IsNull('foo'))];
        yield ['!', new Not(null)];
        yield ['!!', new Not(new Not(null))];
        yield ['0', null];
        yield ['!0', new Not(null)];
        yield ['!!0', new Not(new Not(null))];
        yield [[1, 2], new In('foo', [1, 2])];
        yield ['', null];
        yield [null, null];
        yield [['assoc' => 'array'], null];
    }
}
