<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\ORMResult;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class QueryBuilderObjectResultTest extends ObjectResultTest
{
    protected function createWithItems(int $count): ORMResult
    {
        $this->persistEntities($count);

        return new ORMResult($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e'));
    }
}
