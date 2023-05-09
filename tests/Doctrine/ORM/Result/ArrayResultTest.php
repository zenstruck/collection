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
