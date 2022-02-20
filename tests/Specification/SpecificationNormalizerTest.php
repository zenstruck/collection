<?php

namespace Zenstruck\Collection\Tests\Specification;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\Specification\Join;
use Zenstruck\Collection\Spec;
use Zenstruck\Collection\Specification\Nested;
use Zenstruck\Collection\Specification\SpecificationInterpreter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SpecificationNormalizerTest extends TestCase
{
    /**
     * @test
     */
    public function throws_exception_if_normalizer_not_found(): void
    {
        $normalizer = new SpecificationInterpreter([]);

        $this->assertFalse($normalizer->supports(null, null));

        $this->expectException(\RuntimeException::class);

        $normalizer->interpret(null, null);
    }

    /**
     * @test
     */
    public function can_stringify_spec(): void
    {
        $str = SpecificationInterpreter::stringify(Spec::andX(
            Spec::orX(Spec::eq('foo', 'bar'), Spec::in('foo', ['bar'])),
            Spec::sortAsc('foo'),
            Join::anti('foo'),
            Join::inner('foo'),
            new NestedSpec(),
        ));

        $this->assertSame(
            \sprintf("AndX(OrX(Compare(foo Equal 'bar'), Compare(foo In array)), OrderByASC(foo), AntiJoin(foo), InnerJoin(foo), %s(Compare(foo Equal 1)))", NestedSpec::class),
            $str
        );
    }
}

class NestedSpec implements Nested
{
    public function child(): mixed
    {
        return Spec::eq('foo', 1);
    }
}
