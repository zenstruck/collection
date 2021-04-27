<?php

namespace Zenstruck\Collection\Tests\Specification\Filter;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Specification\Filter\Like;
use Zenstruck\Collection\Specification\Filter\NotLike;
use Zenstruck\Collection\Specification\Filter\WildcardComparison;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class WildcardComparisonTest extends TestCase
{
    /**
     * @test
     * @dataProvider valueDataProvider
     */
    public function value(WildcardComparison $specification, $expectedValue): void
    {
        $this->assertSame($expectedValue, $specification->value());
    }

    public static function valueDataProvider(): iterable
    {
        yield [new Like('field', null), ''];
        yield [new Like('field', 'foo'), 'foo'];
        yield [new Like('field', '%'), '%'];
        yield [new Like('field', '%fo%o%'), '%fo%o%'];
        yield [new Like('field', '%fo*o%'), '%fo*o%'];
        yield [(new Like('field', '%fo*o%'))->allowWildcard(), '%fo%o%'];
        yield [(new Like('field', '%fo&o%'))->allowWildcard('&'), '%fo%o%'];

        yield [Like::contains('field', null), '%%'];
        yield [Like::contains('field', 'foo'), '%foo%'];
        yield [Like::contains('field', '%'), '%%%'];
        yield [Like::contains('field', 'fo%o'), '%fo%o%'];
        yield [Like::contains('field', 'fo*o'), '%fo*o%'];
        yield [Like::contains('field', 'fo*o')->allowWildcard(), '%fo%o%'];
        yield [Like::contains('field', 'fo&o')->allowWildcard('&'), '%fo%o%'];

        yield [Like::beginsWith('field', null), '%'];
        yield [Like::beginsWith('field', 'foo'), 'foo%'];
        yield [Like::beginsWith('field', '%'), '%%'];
        yield [Like::beginsWith('field', 'fo%o'), 'fo%o%'];
        yield [Like::beginsWith('field', 'fo*o'), 'fo*o%'];
        yield [Like::beginsWith('field', 'fo*o')->allowWildcard(), 'fo%o%'];
        yield [Like::beginsWith('field', 'fo&o')->allowWildcard('&'), 'fo%o%'];

        yield [Like::endsWith('field', null), '%'];
        yield [Like::endsWith('field', 'foo'), '%foo'];
        yield [Like::endsWith('field', '%'), '%%'];
        yield [Like::endsWith('field', 'fo%o'), '%fo%o'];
        yield [Like::endsWith('field', 'fo*o'), '%fo*o'];
        yield [Like::endsWith('field', 'fo*o')->allowWildcard(), '%fo%o'];
        yield [Like::endsWith('field', 'fo&o')->allowWildcard('&'), '%fo%o'];

        yield [new NotLike('field', null), ''];
        yield [new NotLike('field', 'foo'), 'foo'];
        yield [new NotLike('field', '%'), '%'];
        yield [new NotLike('field', '%fo%o%'), '%fo%o%'];
        yield [new NotLike('field', '%fo*o%'), '%fo*o%'];
        yield [(new NotLike('field', '%fo*o%'))->allowWildcard(), '%fo%o%'];
        yield [(new NotLike('field', '%fo&o%'))->allowWildcard('&'), '%fo%o%'];

        yield [NotLike::contains('field', null), '%%'];
        yield [NotLike::contains('field', 'foo'), '%foo%'];
        yield [NotLike::contains('field', '%'), '%%%'];
        yield [NotLike::contains('field', 'fo%o'), '%fo%o%'];
        yield [NotLike::contains('field', 'fo*o'), '%fo*o%'];
        yield [NotLike::contains('field', 'fo*o')->allowWildcard(), '%fo%o%'];
        yield [NotLike::contains('field', 'fo&o')->allowWildcard('&'), '%fo%o%'];

        yield [NotLike::beginsWith('field', null), '%'];
        yield [NotLike::beginsWith('field', 'foo'), 'foo%'];
        yield [NotLike::beginsWith('field', '%'), '%%'];
        yield [NotLike::beginsWith('field', 'fo%o'), 'fo%o%'];
        yield [NotLike::beginsWith('field', 'fo*o'), 'fo*o%'];
        yield [NotLike::beginsWith('field', 'fo*o')->allowWildcard(), 'fo%o%'];
        yield [NotLike::beginsWith('field', 'fo&o')->allowWildcard('&'), 'fo%o%'];

        yield [NotLike::endsWith('field', null), '%'];
        yield [NotLike::endsWith('field', 'foo'), '%foo'];
        yield [NotLike::endsWith('field', '%'), '%%'];
        yield [NotLike::endsWith('field', 'fo%o'), '%fo%o'];
        yield [NotLike::endsWith('field', 'fo*o'), '%fo*o'];
        yield [NotLike::endsWith('field', 'fo*o')->allowWildcard(), '%fo%o'];
        yield [NotLike::endsWith('field', 'fo&o')->allowWildcard('&'), '%fo%o'];
    }
}
