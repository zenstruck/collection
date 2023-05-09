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

use Zenstruck\Collection\Doctrine\ORM\EntityResult;
use Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AggregateEntityResultTest extends ObjectResultTest
{
    /**
     * @test
     */
    public function detaches_entity_from_em_on_batch_iterate(): void
    {
        /** @var EntityWithAggregates $result */
        $result = \iterator_to_array($this->createWithItems(2)->batchIterate())[0];

        $this->assertFalse($this->em->contains($result->entity()));
    }

    /**
     * @test
     */
    public function can_batch_update_results(): void
    {
        $result = $this->createWithItems(2);
        $values = \array_map(static fn(EntityWithAggregates $entity) => $entity->entity()->value, \iterator_to_array($result));

        $this->assertSame(['value 1', 'value 2'], $values);

        $batchProcessor = $result->batchProcess();

        $this->assertCount(2, $batchProcessor);

        foreach ($batchProcessor as $item) {
            $item->entity()->value = 'new '.$item->entity()->value;
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

        /** @var EntityWithAggregates[] $batchIterator */
        $batchIterator = $result->batchProcess();

        $this->assertCount(2, $batchIterator);

        foreach ($batchIterator as $item) {
            $this->em->remove($item->entity());
        }

        $this->assertCount(0, $this->em->getRepository(Entity::class)->findAll());
    }

    /**
     * @test
     */
    public function exception_when_iterating_if_result_does_not_have_aggregate_fields(): void
    {
        $this->persistEntities(3);

        $result = (new EntityResult($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e')))
            ->withAggregates()
        ;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Results does not contain aggregate fields, do not call %s::withAggregates().', EntityResult::class));

        \iterator_to_array($result);
    }

    /**
     * @test
     */
    public function exception_when_paginating_if_result_does_not_have_aggregate_fields(): void
    {
        $this->persistEntities(3);

        $result = (new EntityResult($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e')))
            ->withAggregates()
        ;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Results does not contain aggregate fields, do not call %s::withAggregates().', EntityResult::class));

        \iterator_to_array($result->paginate());
    }

    /**
     * @test
     */
    public function exception_when_iterating_if_result_has_aggregate_fields(): void
    {
        $this->persistEntities(3);

        $result = new EntityResult($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e')
            ->addSelect('UPPER(e.value) AS extra')
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Results contain aggregate fields, call %s::withAggregates().', EntityResult::class));

        \iterator_to_array($result);
    }

    /**
     * @test
     */
    public function can_set_as_readonly(): void
    {
        $entity = $this->createWithItems(1)->readonly()->first();

        $this->assertFalse($this->em->contains($entity->entity()));
    }

    protected function createWithItems(int $count): EntityResult
    {
        $this->persistEntities($count);

        $qb = $this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e')
            ->addSelect('UPPER(e.value) AS extra')
        ;

        return (new EntityResult($qb))->withAggregates();
    }

    /**
     * @return EntityWithAggregates<Entity>
     */
    protected function expectedValueAt(int $position): EntityWithAggregates
    {
        return EntityWithAggregates::create([
            0 => new Entity($value = 'value '.$position, $position),
            'extra' => \mb_strtoupper($value),
        ]);
    }
}
