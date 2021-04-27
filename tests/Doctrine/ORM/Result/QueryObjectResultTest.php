<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\ORM\Fixture\KitchenSinkResult;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class QueryObjectResultTest extends ObjectResultTest
{
    protected function createWithItems(int $count): KitchenSinkResult
    {
        $this->persistEntities($count);

        return new KitchenSinkResult($this->em->createQuery(\sprintf('SELECT e FROM %s e', Entity::class)));
    }
}
