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

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\Batch\CountableBatchIterator;
use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Tests\CountableIteratorTests;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Relation;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class EntityRepositoryTest extends TestCase
{
    use CountableIteratorTests, HasDatabase;

    /**
     * @test
     */
    public function can_find(): void
    {
        $repo = $this->createWithItems(3);

        $this->assertEquals($this->expectedValueAt(2), $repo->find(2));
    }

    /**
     * @test
     */
    public function can_find_with_array(): void
    {
        $repo = $this->createWithItems(3);

        $this->assertEquals($this->expectedValueAt(2), $repo->find(['id' => 2]));
    }

    /**
     * @test
     */
    public function can_find_with_criteria(): void
    {
        $repo = $this->createWithItems(3);

        $this->assertEquals($this->expectedValueAt(2), $repo->find(Criteria::create()->where(Criteria::expr()->eq('id', 2))));
    }

    /**
     * @test
     */
    public function can_find_with_callable(): void
    {
        $repo = $this->createWithItems(3);

        $this->assertEquals($this->expectedValueAt(2), $repo->find(function(QueryBuilder $qb, string $root) {
            $qb->andWhere($root.'.id = :id')->setParameter('id', 2);
        }));
    }

    /**
     * @test
     */
    public function find_returns_null_if_nothing_found(): void
    {
        $repo = $this->createWithItems(3);

        $this->assertNull($repo->find(99));
        $this->assertNull($repo->find(['id' => 99]));
        $this->assertNull($repo->find(Criteria::create()->where(Criteria::expr()->eq('id', 99))));
        $this->assertNull($repo->find(function(QueryBuilder $qb, string $root) {
            $qb->andWhere($root.'.id = :id')->setParameter('id', 99);
        }));
    }

    /**
     * @test
     */
    public function can_get_batch_iterator(): void
    {
        $iterator = $this->createWithItems(3)->getIterator();

        $this->assertInstanceOf(CountableBatchIterator::class, $iterator);
        $this->assertCount(3, $iterator);
    }

    /**
     * @test
     */
    public function detaches_entities_from_em_on_iterate(): void
    {
        $iterator = $this->createWithItems(3);

        $result = \iterator_to_array($iterator)[0];

        $this->assertInstanceOf(Entity::class, $result);
        $this->assertFalse($this->em->contains($result));
    }

    /**
     * @test
     */
    public function can_filter_with_array(): void
    {
        $objects = $this->createWithItems(3)->filter(['id' => 2]);

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function can_filter_with_criteria(): void
    {
        $objects = $this->createWithItems(3)->filter(Criteria::create()->where(Criteria::expr()->eq('id', 2)));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function can_filter_with_callable(): void
    {
        $objects = $this->createWithItems(3)->filter(function(QueryBuilder $qb, string $root) {
            $qb->andWhere($root.'.id = :id')->setParameter('id', 2);
        });

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function can_filter_for_no_results(): void
    {
        $repo = $this->createWithItems(3);

        $this->assertEmpty($repo->filter(['id' => 99]));
        $this->assertEmpty($repo->filter(Criteria::create()->where(Criteria::expr()->eq('id', 99))));
        $this->assertEmpty($repo->filter(function(QueryBuilder $qb, string $root) {
            $qb->andWhere($root.'.id = :id')->setParameter('id', 99);
        }));
    }

    protected function createWithItems(int $count): ObjectRepository
    {
        $this->persistEntities($count);

        return $this->repo();
    }

    protected function expectedValueAt(int $position): Entity
    {
        return new Entity("value {$position}", $position);
    }

    protected function repo(): ObjectRepository
    {
        return new EntityRepository($this->em, Entity::class);
    }

    private function persistEntitiesForJoinTest(): void
    {
        $this->em->persist(new Entity('e1'));
        $this->em->persist(Entity::withRelation('e2', new Relation(1)));
        $this->em->persist(Entity::withRelation('e3', new Relation(2)));
        $this->em->persist(new Entity('e4'));
        $this->em->persist(Entity::withRelation('e5', new Relation(3)));
        $this->flushAndClear();
    }
}
