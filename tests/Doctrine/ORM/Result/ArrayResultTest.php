<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\ORM\ResultTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayResultTest extends ResultTest
{
    protected function expectedValueAt(int $position): array
    {
        return [
            'id' => $position,
            'value' => 'value '.$position,
        ];
    }

    protected function createWithItems(int $count): Result
    {
        $this->persistEntities($count);

        return (new Result($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e')))->asArray();
    }
}
