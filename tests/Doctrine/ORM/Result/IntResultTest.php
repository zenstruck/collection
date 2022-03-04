<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\ORM\ResultTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IntResultTest extends ResultTest
{
    /**
     * @test
     */
    public function result_must_be_numeric(): void
    {
        $this->createWithItems(2);

        $result = (new Result($this->em->createQueryBuilder()->select('e.value')->from(Entity::class, 'e')))->asInt();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Expected result(s) to be "int" but got "string".');

        $result->first();
    }

    protected function expectedValueAt(int $position)
    {
        return $position;
    }

    protected function createWithItems(int $count): Result
    {
        $this->persistEntities($count);

        return (new Result($this->em->createQueryBuilder()->select('e.id')->from(Entity::class, 'e')))->asInt();
    }
}
