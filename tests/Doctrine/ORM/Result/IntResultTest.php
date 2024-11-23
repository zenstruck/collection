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
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\ORM\EntityResultTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IntResultTest extends EntityResultTest
{
    /**
     * @test
     */
    public function can_select_single_scalar(): void
    {
        $this->persistEntities(3);
        $result = (new EntityResult($this->em->createQueryBuilder()->select('SUM(e.id)')->from(Entity::class, 'e')))->asInt();

        $this->assertSame(6, $result->first());
    }

    /**
     * @test
     */
    public function iterator_exact_match(): void
    {
        $results = $this->createWithItems(3);

        $this->assertSame([1, 2, 3], \iterator_to_array($results));
    }

    protected function expectedValueAt(int $position)
    {
        return $position;
    }

    protected function createWithItems(int $count): EntityResult
    {
        $this->persistEntities($count);

        return (new EntityResult($this->em->createQueryBuilder()->select('e.id')->from(Entity::class, 'e')))->asInt();
    }
}
