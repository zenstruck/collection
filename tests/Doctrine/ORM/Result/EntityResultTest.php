<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\Batch\CountableBatchIterator;
use Zenstruck\Collection\Doctrine\Batch\CountableBatchProcessor;
use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\ORM\ResultTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class EntityResultTest extends ResultTest
{
    /**
     * @test
     */
    public function can_delete(): void
    {
        $collection = $this->createWithItems(5);

        $this->assertCount(5, $collection);
        $this->assertSame(5, $collection->delete());
        $this->assertCount(0, $collection);
        $this->assertSame([], \iterator_to_array($collection));
    }

    /**
     * @test
     */
    public function can_delete_with_callback(): void
    {
        $deleted = [];
        $collection = $this->createWithItems(2);

        $collection->delete(function(Entity $entity) use (&$deleted) {
            $deleted[] = $entity;
        });

        $this->assertEquals([new Entity('value 1'), new Entity('value 2')], $deleted);
        $this->assertCount(0, $collection);
    }

    /**
     * @test
     */
    public function detaches_entity_from_em_on_batch_iterate(): void
    {
        $result = \iterator_to_array($this->createWithItems(2)->batch())[0];

        $this->assertFalse($this->em->contains($result));
    }

    /**
     * @test
     */
    public function can_batch_update_results(): void
    {
        $result = $this->createWithItems(2);
        $values = \array_map(static fn(Entity $entity) => $entity->value, \iterator_to_array($result));

        $this->assertSame(['value 1', 'value 2'], $values);

        $batchProcessor = $result->batchProcess();

        $this->assertCount(2, $batchProcessor);

        foreach ($batchProcessor as $item) {
            $item->value = 'new '.$item->value;
        }

        $values = \array_map(
            static fn(Entity $entity) => $entity->value,
            $this->em->getRepository(Entity::class)->findAll()
        );

        $this->assertSame(['new value 1', 'new value 2'], $values);
    }

    /**
     * @test
     */
    public function can_batch_delete_results(): void
    {
        $result = $this->createWithItems(2);

        $this->assertCount(2, $result);

        $batchIterator = $result->batchProcess();

        $this->assertCount(2, $batchIterator);

        foreach ($batchIterator as $item) {
            $this->em->remove($item);
        }

        $this->assertCount(0, $this->em->getRepository(Entity::class)->findAll());
    }

    /**
     * @test
     */
    public function batch_iterator_is_countable(): void
    {
        $iterator = $this->createWithItems(3)->batch();

        $this->assertInstanceOf(CountableBatchIterator::class, $iterator);
        $this->assertCount(3, $iterator);
    }

    /**
     * @test
     */
    public function batch_processor_is_countable(): void
    {
        $processor = $this->createWithItems(4)->batchProcess();

        $this->assertInstanceOf(CountableBatchProcessor::class, $processor);
        $this->assertCount(4, $processor);
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
    public function cannot_delete_non_managed_object_results(): void
    {
        $this->persistEntities(3);

        $result = new Result($this->em->createQuery(\sprintf('SELECT e.id FROM %s e', Entity::class)));

        $this->expectException(\LogicException::class);
        $result->delete();
    }

    protected function expectedValueAt(int $position): object
    {
        return new Entity("value {$position}", $position);
    }

    protected function createWithItems(int $count): Result
    {
        $this->persistEntities($count);

        return new Result($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e'));
    }
}
