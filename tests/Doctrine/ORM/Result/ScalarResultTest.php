<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\ORM\ResultTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ScalarResultTest extends ResultTest
{
    protected function expectedValueAt(int $position)
    {
        return "value {$position}";
    }

    protected function createWithItems(int $count): Result
    {
        $this->persistEntities($count);

        return (new Result($this->em->createQueryBuilder()->select('e.value')->from(Entity::class, 'e')))->asScalar();
    }
}
