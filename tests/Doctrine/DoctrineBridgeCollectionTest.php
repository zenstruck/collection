<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;
use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\DoctrineBridgeCollection;
use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\CollectionTests;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Relation;
use Zenstruck\Collection\Tests\MatchableObjectTests;

use function Zenstruck\collect;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DoctrineBridgeCollectionTest extends TestCase
{
    use CollectionTests, HasDatabase, MatchableObjectTests;

    private DoctrineBridgeCollection $collection;

    protected function setUp(): void
    {
        $this->collection = new DoctrineBridgeCollection();
    }

    /**
     * @test
     */
    public function counting_extra_lazy_does_not_initialize_collection(): void
    {
        [$persistentCollection, $entities] = $this->createExtraLazyCollection();

        $this->assertCount(3, $entities);
        $this->assertFalse($persistentCollection->isInitialized());
    }

    /**
     * @test
     */
    public function first_extra_lazy_does_not_initialize_collection(): void
    {
        [$persistentCollection, $entities] = $this->createExtraLazyCollection();

        $this->assertEquals('value 1', $entities->first()->value);
        $this->assertFalse($persistentCollection->isInitialized());
    }

    /**
     * @test
     */
    public function take_extra_lazy_does_not_initialize_collection(): void
    {
        [$persistentCollection, $entities] = $this->createExtraLazyCollection();

        $this->assertCount(2, $entities->take(2));
        $this->assertFalse($persistentCollection->isInitialized());
    }

    /**
     * @test
     */
    public function iterating_extra_lazy_does_not_initialize_collection(): void
    {
        [$persistentCollection, $entities] = $this->createExtraLazyCollection();

        foreach ($entities as $entity) {
            $this->assertInstanceof(Entity::class, $entity);
        }

        $this->assertFalse($persistentCollection->isInitialized());
    }

    /**
     * @test
     */
    public function can_use_criteria_as_filter_specification(): void
    {
        $collection = $this->createWithItems(10);
        $criteria = Criteria::create()->where(Criteria::expr()->lt('id', 5))
            ->orderBy(['id' => Criteria::DESC])
        ;

        $this->assertSame('value 4', $collection->filter($criteria)->first()->value);
    }

    /**
     * @test
     */
    public function can_use_criteria_as_find_specification(): void
    {
        $collection = $this->createWithItems(10);
        $criteria = Criteria::create()->where(Criteria::expr()->lt('id', 5))
            ->orderBy(['id' => Criteria::DESC])
        ;

        $this->assertSame('value 4', $collection->find($criteria)->value);
    }

    /**
     * @test
     */
    public function key_current_next(): void
    {
        $this->collection[] = 'a';
        $this->collection[] = 'b';

        $this->assertSame(0, $this->collection->key());
        $this->assertSame('a', $this->collection->current());
        $this->assertSame('b', $this->collection->next());
    }

    /**
     * @test
     */
    public function isset_and_unset(): void
    {
        $this->assertFalse(isset($this->collection[0]));
        $this->collection->add('testing');
        $this->assertTrue(isset($this->collection[0]));
        unset($this->collection[0]);
        $this->assertFalse(isset($this->collection[0]));
    }

    /**
     * @test
     */
    public function removing_non_existent_entry_returns_null(): void
    {
        $this->assertNull($this->collection->remove('testing_does_not_exist'));
    }

    /**
     * @test
     */
    public function exists(): void
    {
        $this->collection->add('one');
        $this->collection->add('two');
        $exists = $this->collection->exists(static fn($k, $e) => 'one' === $e);
        $this->assertTrue($exists);
        $exists = $this->collection->exists(static fn($k, $e) => 'other' === $e);
        $this->assertFalse($exists);
    }

    /**
     * @test
     */
    public function first_and_last(): void
    {
        $this->collection->add('one');
        $this->collection->add('two');

        $this->assertEquals($this->collection->first(), 'one');
        $this->assertEquals($this->collection->last(), 'two');
    }

    /**
     * @test
     */
    public function array_access(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';

        $this->assertEquals($this->collection[0], 'one');
        $this->assertEquals($this->collection[1], 'two');

        unset($this->collection[0]);
        $this->assertEquals($this->collection->count(), 1);
    }

    /**
     * @test
     */
    public function contains_key(): void
    {
        $this->collection[5] = 'five';
        $this->assertTrue($this->collection->containsKey(5));
    }

    /**
     * @test
     */
    public function call_contains(): void
    {
        $this->collection[0] = 'test';
        $this->assertTrue($this->collection->contains('test'));
    }

    /**
     * @test
     */
    public function search(): void
    {
        $this->collection[0] = 'test';
        $this->assertEquals(0, $this->collection->indexOf('test'));
    }

    /**
     * @test
     */
    public function get(): void
    {
        $this->collection[0] = 'test';
        $this->assertEquals('test', $this->collection->get(0));
    }

    /**
     * @test
     */
    public function get_keys(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $this->assertEquals([0, 1], $this->collection->getKeys());
    }

    /**
     * @test
     */
    public function get_values(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $this->assertEquals(['one', 'two'], $this->collection->getValues());
    }

    /**
     * @test
     */
    public function can_count(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $this->assertEquals($this->collection->count(), 2);
        $this->assertEquals(\count($this->collection), 2);
    }

    /**
     * @test
     */
    public function for_all(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $this->assertEquals($this->collection->forAll(static fn($k, $e) => \is_string($e)), true);
        $this->assertEquals($this->collection->forAll(static fn($k, $e) => \is_array($e)), false);
    }

    /**
     * @test
     */
    public function partition(): void
    {
        $this->collection[] = true;
        $this->collection[] = false;
        $partition = $this->collection->partition(static fn($k, $e) => true === $e);
        $this->assertEquals($partition[0][0], true);
        $this->assertEquals($partition[1][0], false);
    }

    /**
     * @test
     */
    public function clear(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $this->collection->clear();
        $this->assertEquals($this->collection->isEmpty(), true);
    }

    /**
     * @test
     */
    public function remove(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $el = $this->collection->remove(0);

        $this->assertEquals('one', $el);
        $this->assertEquals($this->collection->contains('one'), false);
        $this->assertNull($this->collection->remove(0));
    }

    /**
     * @test
     */
    public function remove_element(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';

        $this->assertTrue($this->collection->removeElement('two'));
        $this->assertFalse($this->collection->contains('two'));
        $this->assertFalse($this->collection->removeElement('two'));
    }

    /**
     * @test
     */
    public function slice(): void
    {
        $this->collection[] = 'one';
        $this->collection[] = 'two';
        $this->collection[] = 'three';

        $slice = $this->collection->slice(0, 1);
        $this->assertIsArray($slice);
        $this->assertEquals(['one'], $slice);

        $slice = $this->collection->slice(1);
        $this->assertEquals([1 => 'two', 2 => 'three'], $slice);

        $slice = $this->collection->slice(1, 1);
        $this->assertEquals([1 => 'two'], $slice);
    }

    /**
     * @test
     */
    public function can_remove_null_values_by_key(): void
    {
        $this->collection->add(null);
        $this->collection->remove(0);
        $this->assertTrue($this->collection->isEmpty());
    }

    /**
     * @test
     */
    public function can_verify_existing_keys_with_null_values(): void
    {
        $this->collection->set('key', null);
        $this->assertTrue($this->collection->containsKey('key'));
    }

    protected function expectedValueAt(int $position): object
    {
        return new Entity("value {$position}", $position);
    }

    protected function createWithItems(int $count): DoctrineBridgeCollection
    {
        $this->persistEntities($count);

        return new DoctrineBridgeCollection($this->em->getRepository(Entity::class)->matching(new Criteria()));
    }

    /**
     * @return array{0:PersistentCollection,1:LazyCollection}
     */
    private function createExtraLazyCollection(): array
    {
        $this->setupEntityManager();

        $this->em->persist($relation = new Relation(1));
        $this->em->persist(Entity::withRelation('value 1', $relation));
        $this->em->persist(Entity::withRelation('value 2', $relation));
        $this->em->persist(Entity::withRelation('value 3', $relation));

        $this->flushAndClear();

        return [
            $persistentCollection = $this->em->find(Relation::class, 1)->getEntities(),
            collect($persistentCollection),
        ];
    }
}
