<?php

namespace Zenstruck\Collection\Tests\Doctrine\DBAL;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\Result;
use Zenstruck\Collection\Tests\CollectionTests;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ResultTest extends TestCase
{
    use CollectionTests, HasDatabase;

    protected function createWithItems(int $count): Collection
    {
        $this->persistEntities($count);

        return new Result($this->em->getConnection()->createQueryBuilder()->select('*')->from(Entity::TABLE, 'e'));
    }

    protected function expectedValueAt(int $position): array
    {
        return ['id' => $position, 'value' => 'value '.$position, 'relation_id' => null];
    }
}
