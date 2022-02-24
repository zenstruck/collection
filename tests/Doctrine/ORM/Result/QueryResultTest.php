<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\ORMResult;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class QueryResultTest extends ResultTest
{
    /**
     * @test
     */
    public function cannot_delete_non_managed_object_results(): void
    {
        $this->persistEntities(3);

        $result = new ORMResult($this->em->createQuery(\sprintf('SELECT e.id FROM %s e', Entity::class)));

        $this->expectException(\LogicException::class);
        $result->delete();
    }

    protected function createWithItems(int $count): ORMResult
    {
        $this->persistEntities($count);

        return new ORMResult($this->em->createQuery(\sprintf('SELECT e FROM %s e', Entity::class)));
    }
}
