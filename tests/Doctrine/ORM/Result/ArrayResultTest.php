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
final class ArrayResultTest extends EntityResultTest
{
    /**
     * @test
     */
    public function can_select_single(): void
    {
        $this->persistEntities(3);
        $result = (new EntityResult($this->em->createQueryBuilder()->select('SUM(e.id), SUM(e.id)')->from(Entity::class, 'e')))->asArray();

        $this->assertSame([6, 6], \array_map(static fn($v) => (int) $v, \array_values($result->first())));
    }

    protected function expectedValueAt(int $position): array
    {
        return [
            'id' => $position,
            'value' => 'value '.$position,
        ];
    }

    protected function createWithItems(int $count): EntityResult
    {
        $this->persistEntities($count);

        return (new EntityResult($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e')))->asArray();
    }
}
