<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\EntityResultQueryBuilder;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SingleScalarTest extends TestCase
{
    use HasDatabase;

    /**
     * @test
     */
    public function can_get_single_scalar_value_on_filter(): void
    {
        $this->persistEntities(4);

        $result = EntityResultQueryBuilder::forEntity($this->em, Entity::class, 'e')
            ->select('SUM(e.id)')
            ->result()
        ;

        $this->assertEquals(10, $result->asScalar()->first());
        $this->assertSame(10, $result->asInt()->first());
        $this->assertSame(10.0, $result->asFloat()->first());
        $this->assertSame('10', $result->asString()->first());
    }

    /**
     * @test
     */
    public function can_get_single_scalar_value_on_delete(): void
    {
        $this->persistEntities(4);

        $result = EntityResultQueryBuilder::forEntity($this->em, Entity::class, 'e')
            ->delete()
            ->where('e.id > 1')
            ->result()
        ;

        $this->assertEquals(3, $result->asScalar()->first());
        $this->assertSame(0, $result->asInt()->first());
        $this->assertSame(0.0, $result->asFloat()->first());
        $this->assertSame('0', $result->asString()->first());
    }

    /**
     * @test
     */
    public function can_get_single_scalar_value_on_update(): void
    {
        $this->persistEntities(4);

        $result = EntityResultQueryBuilder::forEntity($this->em, Entity::class, 'e')
            ->update()
            ->set('e.value', ':value')
            ->setParameter('value', 'foo')
            ->where('e.id > 1')
            ->result()
        ;

        $this->assertEquals(3, $result->asScalar()->first());
        $this->assertSame(3, $result->asInt()->first());
        $this->assertSame(3.0, $result->asFloat()->first());
        $this->assertSame('3', $result->asString()->first());
    }

    /**
     * @test
     */
    public function not_a_collection_eager(): void
    {
        $result = EntityResultQueryBuilder::forEntity($this->em, Entity::class, 'e')
            ->delete()
            ->where('e.id > 1')
            ->result()
        ;

        $this->expectException(\LogicException::class);

        $result->eager()->all();
    }

    /**
     * @test
     */
    public function not_a_collection_iterate(): void
    {
        $result = EntityResultQueryBuilder::forEntity($this->em, Entity::class, 'e')
            ->delete()
            ->where('e.id > 1')
            ->result()
        ;

        $this->expectException(\LogicException::class);

        foreach ($result as $item) {
        }
    }
}
