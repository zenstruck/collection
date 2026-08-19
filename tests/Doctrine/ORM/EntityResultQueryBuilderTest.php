<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine\ORM;

use Doctrine\ORM\Query;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Zenstruck\Collection\Doctrine\ORM\EntityResultQueryBuilder;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class EntityResultQueryBuilderTest extends TestCase
{
    use HasDatabase;

    /**
     * @test
     */
    public function can_modify_the_query(): void
    {
        $this->persistEntities(3);

        $result = EntityResultQueryBuilder::forEntity($this->em, Entity::class, 'e')
            ->modifyQuery(static fn(Query $query) => $query->setMaxResults(2))
            ->result()
        ;

        $this->assertCount(2, $result->eager());
    }

    /**
     * @test
     */
    public function can_cache_the_result(): void
    {
        $this->em->getConfiguration()->setResultCache(new ArrayAdapter());
        $this->persistEntities(3);

        $result = EntityResultQueryBuilder::forEntity($this->em, Entity::class, 'e')
            ->cacheResult(60, 'my-key')
            ->result()
        ;

        $this->assertCount(3, $result->eager());

        $this->assertQueryCount(0, function() use ($result) {
            $this->assertCount(3, $result->eager());
        });
    }

    /**
     * @test
     */
    public function can_mark_as_readonly(): void
    {
        $this->persistEntities(1);

        $entity = EntityResultQueryBuilder::forEntity($this->em, Entity::class, 'e')
            ->readonly()
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;

        $this->assertTrue($this->em->getUnitOfWork()->isReadOnly($entity));
    }

    /**
     * @test
     */
    public function result_is_marked_as_readonly(): void
    {
        $this->persistEntities(1);

        $entity = EntityResultQueryBuilder::forEntity($this->em, Entity::class, 'e')
            ->readonly()
            ->setMaxResults(1)
            ->result()
            ->first()
        ;

        $this->assertFalse($this->em->contains($entity));
    }
}
