<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\ORM\Result;
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

        $result = new Result($this->em->createQuery(\sprintf('SELECT e.id FROM %s e', Entity::class)));

        $this->expectException(\LogicException::class);
        $result->delete();
    }

    protected function createWithItems(int $count): Result
    {
        $this->persistEntities($count);

        return new Result($this->em->createQuery(\sprintf('SELECT e FROM %s e', Entity::class)));
    }
}
