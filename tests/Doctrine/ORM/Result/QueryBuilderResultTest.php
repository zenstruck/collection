<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class QueryBuilderResultTest extends ResultTest
{
    protected function createWithItems(int $count): Result
    {
        $this->persistEntities($count);

        return new Result($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e'));
    }
}
