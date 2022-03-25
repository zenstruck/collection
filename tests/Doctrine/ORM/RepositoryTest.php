<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\Batch\CountableBatchIterator;
use Zenstruck\Collection\Doctrine\Batch\CountableBatchProcessor;
use Zenstruck\Collection\Doctrine\ORM\Specification\Join;
use Zenstruck\Collection\Spec;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Relation;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;
use Zenstruck\Collection\Tests\Doctrine\ORM\Fixture\DummyManagerRegistry;
use Zenstruck\Collection\Tests\Doctrine\ORM\Fixture\KitchenSinkRepository;
use Zenstruck\Collection\Tests\Doctrine\SpecificationRepositoryTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RepositoryTest extends TestCase
{
    use HasDatabase, SpecificationRepositoryTests;

    /**
     * @test
     */
    public function can_find(): void
    {
        $repo = $this->createWithItems(3);

        $this->assertEquals($this->expectedValueAt(2), $repo->find(2));
        $this->assertNull($repo->find(99));
    }

    /**
     * @test
     */
    public function can_find_all(): void
    {
        $repo = $this->createWithItems(2);

        $this->assertEquals([$this->expectedValueAt(1), $this->expectedValueAt(2)], $repo->findAll());
    }

    /**
     * @test
     */
    public function find_all_is_empty_if_repository_is_empty(): void
    {
        $this->assertSame([], $this->createWithItems(0)->findAll());
    }

    /**
     * @test
     */
    public function can_find_by(): void
    {
        $repo = $this->createWithItems(2);

        $this->assertEquals([$this->expectedValueAt(1), $this->expectedValueAt(2)], $repo->findBy([]));
        $this->assertEquals([$this->expectedValueAt(2), $this->expectedValueAt(1)], $repo->findBy([], ['id' => 'DESC']));
        $this->assertEquals([$this->expectedValueAt(2)], $repo->findBy([], ['id' => 'DESC'], 1));
        $this->assertEquals([$this->expectedValueAt(1)], $repo->findBy([], ['id' => 'DESC'], 1, 1));
        $this->assertEquals([$this->expectedValueAt(2)], $repo->findBy(['id' => 2]));
        $this->assertSame([], $repo->findBy(['id' => 99]));
    }

    /**
     * @test
     */
    public function can_find_one_by(): void
    {
        $repo = $this->createWithItems(2);

        $this->assertEquals($this->expectedValueAt(2), $repo->findOneBy(['id' => 2]));
        $this->assertNull($repo->findOneBy(['id' => 99]));
    }

    /**
     * @test
     */
    public function can_call_method_on_inner_entity_repository(): void
    {
        $this->assertInstanceOf(QueryBuilder::class, $this->createWithItems(0)->createQueryBuilder('e'));
        $this->assertInstanceOf(Entity::class, $this->createWithItems(1)->findOneByValue('value 1'));
    }

    /**
     * @test
     */
    public function can_add_and_flush(): void
    {
        $repo = $this->createWithItems(0);

        $this->assertCount(0, $repo);

        $repo->add(new Entity('foo'), false);
        $repo->add(new Entity('bar'), false);
        $repo->flush();
        $repo->add(new Entity('baz'));

        $this->assertCount(3, $repo);
    }

    /**
     * @test
     */
    public function can_save_entities(): void
    {
        $repo = $this->createWithItems(0);

        $this->assertCount(0, $repo);

        $repo->save(new Entity('foo'));
        $repo->save(new Entity('bar'));
        $repo->save(new Entity('baz'));

        $this->assertCount(3, $repo);
    }

    /**
     * @test
     */
    public function can_remove_and_flush(): void
    {
        $repo = $this->createWithItems(3);
        $items = $repo->findAll();

        $this->assertCount(3, $repo);

        $repo->remove($items[0], false);
        $repo->remove($items[1], false);
        $repo->flush();
        $repo->remove($items[2]);

        $this->assertEmpty($repo);
    }

    /**
     * @test
     */
    public function can_get_batch_iterator(): void
    {
        $iterator = $this->createWithItems(3)->batch();

        $this->assertInstanceOf(CountableBatchIterator::class, $iterator);
        $this->assertCount(3, $iterator);
    }

    /**
     * @test
     */
    public function can_get_batch_processor(): void
    {
        $iterator = $this->createWithItems(3)->batchProcess();

        $this->assertInstanceOf(CountableBatchProcessor::class, $iterator);
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

    /**
     * @test
     */
    public function can_get(): void
    {
        $object = $this->createWithItems(3)->get(2);

        $this->assertSame('value 2', $object->value);
    }

    /**
     * @test
     */
    public function get_fails_if_not_found(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('"%s" with id "invalid" not found.', Entity::class));

        $this->repo()->get('invalid');
    }

    /**
     * @test
     */
    public function can_filter(): void
    {
        $objects = $this->createWithItems(3)->filter(['id' => 2]);

        $this->assertCount(1, $objects);
        $this->assertSame('value 2', \iterator_to_array($objects)[0]->value);
    }

    protected function createWithItems(int $count): KitchenSinkRepository
    {
        $this->persistEntities($count);

        return $this->repo();
    }

    protected function expectedValueAt(int $position): Entity
    {
        return new Entity("value {$position}", $position);
    }

    protected function repo(): KitchenSinkRepository
    {
        return new KitchenSinkRepository(new DummyManagerRegistry($this->em));
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
