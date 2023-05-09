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
final class QueryFieldsResultTest extends EntityResultTest
{
    /**
     * @test
     */
    public function can_batch_iterate(): void
    {
        $result = $this->createWithItems(2)->batchIterate();

        $this->assertCount(2, $result);
        $this->assertSame([
            [
                'id' => 1,
                'my_value' => 'value 1',
            ],
            [
                'id' => 2,
                'my_value' => 'value 2',
            ],
        ], \iterator_to_array($result));
    }

    protected function createWithItems(int $count): EntityResult
    {
        $this->persistEntities($count);

        $qb = $this->em->createQueryBuilder()->select('e.id, e.value AS my_value')->from(Entity::class, 'e');

        return (new EntityResult($qb))->asArray();
    }

    protected function expectedValueAt(int $position): array
    {
        return [
            'id' => $position,
            'my_value' => 'value '.$position,
        ];
    }
}
