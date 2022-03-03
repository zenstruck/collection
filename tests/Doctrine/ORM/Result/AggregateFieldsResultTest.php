<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates;
use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;
use Zenstruck\Collection\Tests\PagintableCollectionTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AggregateFieldsResultTest extends ResultTest
{
    use HasDatabase, PagintableCollectionTests;

    /**
     * @test
     */
    public function detaches_entity_from_em_on_batch_iterate(): void
    {
        /** @var EntityWithAggregates $result */
        $result = \iterator_to_array($this->createWithItems(2)->batch())[0];

        $this->assertFalse($this->em->contains($result->entity()));
    }

    /**
     * @test
     */
    public function can_batch_update_results(): void
    {
        $result = $this->createWithItems(2);
        $values = \array_map(static fn(EntityWithAggregates $entity) => $entity->value, \iterator_to_array($result));

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
    public function detaches_entities_from_em_on_iterate(): void
    {
        $iterator = $this->createWithItems(3);

        $result = \iterator_to_array($iterator)[0];

        $this->assertInstanceOf(EntityWithAggregates::class, $result);
        $this->assertFalse($this->em->contains($result->entity()));
    }

    /**
     * @test
     */
    public function exception_when_iterating_if_result_does_not_have_aggregate_fields(): void
    {
        $this->persistEntities(3);

        $result = (new Result($this->em->createQuery(\sprintf('SELECT e FROM %s e', Entity::class))))
            ->withAggregates()
        ;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Results does not contain aggregate fields, do not call %s::withAggregates().', Result::class));

        \iterator_to_array($result);
    }

    /**
     * @test
     */
    public function exception_when_paginating_if_result_does_not_have_aggregate_fields(): void
    {
        $this->persistEntities(3);

        $result = (new Result($this->em->createQuery(\sprintf('SELECT e FROM %s e', Entity::class))))
            ->withAggregates()
        ;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Results does not contain aggregate fields, do not call %s::withAggregates().', Result::class));

        \iterator_to_array($result->paginate());
    }

    /**
     * @test
     */
    public function exception_when_iterating_if_result_has_aggregate_fields(): void
    {
        $this->persistEntities(3);

        $result = new Result($this->em->createQuery(\sprintf('SELECT e, UPPER(e.value) AS extra FROM %s e', Entity::class)));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Results contain aggregate fields, call %s::withAggregates().', Result::class));

        \iterator_to_array($result);
    }

    /**
     * @test
     */
    public function exception_when_paginating_if_result_has_aggregate_fields(): void
    {
        $this->markTestIncomplete('Revisit once Result only allows objects.');

        $this->persistEntities(3);

        $result = new Result($this->em->createQuery(\sprintf('SELECT e, UPPER(e.value) AS extra FROM %s e', Entity::class)));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('Results contain aggregate fields, call %s::withAggregates().', Result::class));

        \iterator_to_array($result->paginate());
    }

    protected function createWithItems(int $count): Result
    {
        $this->persistEntities($count);

        $query = $this->em->createQuery(\sprintf('SELECT e, UPPER(e.value) AS extra FROM %s e', Entity::class));

        return (new Result($query))->withAggregates();
    }

    /**
     * @return EntityWithAggregates<Entity>
     */
    protected function expectedValueAt(int $position): EntityWithAggregates
    {
        return new EntityWithAggregates(
            new Entity($value = 'value '.$position, $position),
            ['extra' => \mb_strtoupper($value)]
        );
    }
}
