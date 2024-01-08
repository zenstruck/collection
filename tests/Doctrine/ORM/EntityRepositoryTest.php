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
use Zenstruck\Collection\Doctrine\DoctrineSpec;
use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Specification\Join;
use Zenstruck\Collection\Exception\InvalidSpecification;
use Zenstruck\Collection\Spec;
use Zenstruck\Collection\Tests\CountableIteratorTests;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Relation;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;
use Zenstruck\Collection\Tests\MatchableObjectTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class EntityRepositoryTest extends TestCase
{
    use CountableIteratorTests, HasDatabase, MatchableObjectTests;

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
        $objects = $this->createWithItems(3)->query(['id' => 2]);

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function can_filter_with_none(): void
    {
        $objects = $this->createWithItems(3)->query(null);

        $this->assertCount(3, $objects);
    }

    /**
     * @test
     */
    public function can_filter_with_criteria(): void
    {
        $objects = $this->createWithItems(3)->query(Criteria::create()->where(Criteria::expr()->eq('id', 2)));

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function can_filter_with_callable(): void
    {
        $objects = $this->createWithItems(3)->query(function(QueryBuilder $qb, string $root) {
            $qb->andWhere($root.'.id = :id')->setParameter('id', 2);
        });

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    /**
     * @test
     */
    public function cannot_find_with_callable_strings(): void
    {
        $this->assertIsCallable('system');
        $this->assertNull($this->repo()->find('system'));
    }

    /**
     * @test
     */
    public function cannot_query_with_callable_strings(): void
    {
        $this->assertIsCallable('system');

        $repo = $this->repo();

        $this->expectExceptionMessage(\sprintf('"%s::query()" does not support specification "system (string)". Only array|Criteria|callable(QueryBuilder) supported.', EntityRepository::class));
        $this->expectException(InvalidSpecification::class);

        $repo->query('system');
    }

    /**
     * @test
     */
    public function can_filter_for_no_results(): void
    {
        $repo = $this->createWithItems(3);

        $this->assertEmpty($repo->query(['id' => 99]));
        $this->assertEmpty($repo->query(Criteria::create()->where(Criteria::expr()->eq('id', 99))));
        $this->assertEmpty($repo->query(function(QueryBuilder $qb, string $root) {
            $qb->andWhere($root.'.id = :id')->setParameter('id', 99);
        }));
    }

    /**
     * @test
     */
    public function filter_contains_wildcard(): void
    {
        $repo = $this->createWithItems(3);

        $results = $repo->filter(Spec::contains('value', 'v*ue 2'));
        $this->assertCount(1, $results);
        $this->assertSame('value 2', $results->first()->value);

        $results = $repo->filter(Spec::contains('value', '*e 2'));
        $this->assertCount(1, $results);
        $this->assertSame('value 2', $results->first()->value);

        $results = $repo->filter(Spec::contains('value', '*'));
        $this->assertCount(3, $results);

        $results = $repo->filter(Spec::contains('value', 'lue *'));
        $this->assertCount(3, $results);
    }

    /**
     * @test
     */
    public function filter_starts_with_wildcard(): void
    {
        $repo = $this->createWithItems(3);

        $results = $repo->filter(Spec::contains('value', 'v*ue'));
        $this->assertCount(3, $results);

        $results = $repo->filter(Spec::contains('value', 'v*ue*'));
        $this->assertCount(3, $results);
    }

    /**
     * @test
     */
    public function filter_ends_with_wildcard(): void
    {
        $repo = $this->createWithItems(3);

        $results = $repo->filter(Spec::contains('value', 'l*e 2'));
        $this->assertCount(1, $results);
        $this->assertSame('value 2', $results->first()->value);

        $results = $repo->filter(Spec::contains('value', '*l*e 2'));
        $this->assertCount(1, $results);
        $this->assertSame('value 2', $results->first()->value);
    }

    /**
     * @test
     */
    public function readonly_specification(): void
    {
        $results = $this->createWithItems(3)->filter(DoctrineSpec::readonly());

        $this->assertCount(3, $results);
        $this->assertFalse($this->em->contains($results->first()));
    }

    /**
     * @test
     */
    public function delete_specification(): void
    {
        $repo = $this->createWithItems(3);

        $result = $repo
            ->filter(DoctrineSpec::andX(DoctrineSpec::gt('id', 1), DoctrineSpec::delete()))
            ->first()
        ;

        $this->assertSame(2, $result);
        $this->assertCount(1, $repo->filter(null));
        $this->assertSame('value 1', $repo->filter(null)->first()->value);
    }

    /**
     * @test
     */
    public function filter_with_inner_join(): void
    {
        $this->persistEntitiesForJoinTest();

        $this->assertCount(5, $this->repo());

        $results = \iterator_to_array($this->repo()->filter(Join::inner('relation')));

        $this->assertCount(3, $results);
        $this->assertQueryCount(2, function() use ($results) {
            $this->assertSame(2, $results[1]->relation->value);
            $this->assertSame(3, $results[2]->relation->value);
        });
    }

    /**
     * @test
     */
    public function filter_with_eager_inner_join(): void
    {
        $this->persistEntitiesForJoinTest();

        $this->assertCount(5, $this->repo());

        $results = \iterator_to_array($this->repo()->filter(Join::inner('relation')->eager()));

        $this->assertCount(3, $results);
        $this->assertQueryCount(0, function() use ($results) {
            $this->assertSame(2, $results[1]->relation->value);
            $this->assertSame(3, $results[2]->relation->value);
        });
    }

    /**
     * @test
     */
    public function filter_with_left_join(): void
    {
        $this->persistEntitiesForJoinTest();

        $this->assertCount(5, $this->repo());

        $results = \iterator_to_array($this->repo()->filter(Join::left('relation')));

        $this->assertCount(5, $results);
        $this->assertQueryCount(2, function() use ($results) {
            $this->assertSame(1, $results[1]->relation->value);
            $this->assertSame(2, $results[2]->relation->value);
            $this->assertNull($results[3]->relation);
        });
    }

    /**
     * @test
     */
    public function filter_with_eager_left_join(): void
    {
        $this->persistEntitiesForJoinTest();

        $this->assertCount(5, $this->repo());

        $results = \iterator_to_array($this->repo()->filter(Join::left('relation')->eager()));

        $this->assertCount(5, $results);
        $this->assertQueryCount(0, function() use ($results) {
            $this->assertSame(1, $results[1]->relation->value);
            $this->assertSame(2, $results[2]->relation->value);
            $this->assertNull($results[3]->relation);
        });
    }

    /**
     * @test
     */
    public function filter_with_join_and_scoped_select(): void
    {
        $this->persistEntitiesForJoinTest();

        $this->assertCount(5, $this->repo());

        $results = \iterator_to_array($this->repo()->filter(Join::inner('relation')->scope(
            Spec::andX(Spec::gt('value', 1), Spec::lt('value', 3))
        )));

        $this->assertCount(1, $results);
        $this->assertQueryCount(1, function() use ($results) {
            $this->assertSame(2, $results[0]->relation->value);
        });
    }

    /**
     * @test
     */
    public function filter_with_join_and_eager_scoped_select(): void
    {
        $this->persistEntitiesForJoinTest();

        $this->assertCount(5, $this->repo());

        $results = \iterator_to_array($this->repo()->filter(Join::inner('relation')->eager()->scope(
            Spec::andX(Spec::gt('value', 1), Spec::lt('value', 3))
        )));

        $this->assertCount(1, $results);
        $this->assertQueryCount(0, function() use ($results) {
            $this->assertSame(2, $results[0]->relation->value);
        });
    }

    /**
     * @test
     */
    public function filter_with_left_anti_join(): void
    {
        $this->persistEntitiesForJoinTest();

        $this->assertCount(5, $this->repo());

        $results = \iterator_to_array($this->repo()->filter(Join::anti('relation')));

        $this->assertCount(2, $results);
    }

    /**
     * @test
     */
    public function filter_with_join_and_multiple_scope(): void
    {
        $this->persistEntitiesForJoinTest();

        $this->assertCount(5, $this->repo());

        $results = $this->repo()->filter(
            Spec::andX(
                Join::inner('relation')->eager()->scope(Spec::gt('value', 1)),
                Join::inner('relation')->eager()->scope(Spec::lt('value', 3))
            )
        );

        $this->assertCount(1, $results);

        $results = \iterator_to_array($results);

        $this->assertQueryCount(0, function() use ($results) {
            $this->assertSame(2, $results[0]->relation->value);
        });
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
