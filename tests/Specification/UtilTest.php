<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Specification;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Spec;
use Zenstruck\Collection\Specification\Filter\Between;
use Zenstruck\Collection\Specification\Nested;
use Zenstruck\Collection\Specification\Util;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UtilTest extends TestCase
{
    /**
     * @test
     */
    public function stringify_stringable_specifications(): void
    {
        $this->assertSame("Compare(name EqualTo 'kevin')", Util::stringify(Spec::eq('name', 'kevin')));
        $this->assertSame('Compare(views GreaterThan 100)', Util::stringify(Spec::gt('views', 100)));
        $this->assertSame('IsNull(deletedAt)', Util::stringify(Spec::isNull('deletedAt')));
        $this->assertSame('OrderBy(name)', Util::stringify(Spec::sortAsc('name')));
        $this->assertSame('Between[1 AND 5]', Util::stringify(Between::inclusive('id', 1, 5)));
        $this->assertSame('Between(1 AND 5)', Util::stringify(Between::exclusive('id', 1, 5)));
    }

    /**
     * @test
     */
    public function stringify_nested_specifications(): void
    {
        $this->assertSame(
            \sprintf("%s(Compare(status EqualTo 'published'))", NestedFixture::class),
            Util::stringify(new NestedFixture()),
        );
    }

    /**
     * @test
     */
    public function stringify_anything_else(): void
    {
        $this->assertSame('string', Util::stringify('foo'));
        $this->assertSame('int', Util::stringify(1));
        $this->assertSame('null', Util::stringify(null));
        $this->assertSame(\stdClass::class, Util::stringify(new \stdClass()));
    }
}

final class NestedFixture implements Nested
{
    public function specification(): mixed
    {
        return Spec::eq('status', 'published');
    }
}
