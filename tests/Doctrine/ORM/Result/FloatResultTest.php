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
final class FloatResultTest extends EntityResultTest
{
    /**
     * @test
     */
    public function null_result(): void
    {
        $result = (new EntityResult($this->em->createQueryBuilder()->select('AVG(e.id)')->from(Entity::class, 'e')))->asFloat();

        $this->assertNull($result->first());
        $this->assertSame(0.0, $result->first(0.0));
        $this->assertSame([], \array_filter($result->eager()->all()));
    }

    /**
     * @test
     */
    public function iterator_exact_match(): void
    {
        $results = $this->createWithItems(3);

        $this->assertSame([1.0, 2.0, 3.0], \iterator_to_array($results));
    }

    protected function expectedValueAt(int $position)
    {
        return (float) $position;
    }

    protected function createWithItems(int $count): EntityResult
    {
        $this->persistEntities($count);

        return (new EntityResult($this->em->createQueryBuilder()->select('e.id')->from(Entity::class, 'e')))->asFloat();
    }
}
