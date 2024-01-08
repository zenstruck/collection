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

use Doctrine\Common\Collections\Criteria;
use Zenstruck\Collection\Doctrine\Batch\CountableBatchIterator;
use Zenstruck\Collection\Doctrine\Batch\CountableBatchProcessor;
use Zenstruck\Collection\Doctrine\ORM\EntityResult;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\ORM\EntityResultTest;
use Zenstruck\Collection\Tests\MatchableObjectTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class ObjectResultTest extends EntityResultTest
{
    use MatchableObjectTests;

    /**
     * @test
     */
    public function detaches_entity_from_em_on_batch_iterate(): void
    {
        $result = \iterator_to_array($this->createWithItems(2)->batchIterate())[0];

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
            $this->em->getRepository(Entity::class)->findAll(),
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
        $iterator = $this->createWithItems(3)->batchIterate();

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
    public function can_set_as_readonly(): void
    {
        $entity = $this->createWithItems(1)->readonly()->first();

        $this->assertFalse($this->em->contains($entity));
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

        $this->assertEquals($this->expectedValueAt(4), $collection->filter($criteria)->first());
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

        $this->assertEquals($this->expectedValueAt(4), $collection->find($criteria));
    }

    protected function expectedValueAt(int $position): object
    {
        return new Entity("value {$position}", $position);
    }

    protected function createWithItems(int $count): EntityResult
    {
        $this->persistEntities($count);

        return new EntityResult($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e'));
    }
}
