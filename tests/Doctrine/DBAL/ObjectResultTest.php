<?php

namespace Zenstruck\Collection\Tests\Doctrine\DBAL;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\ObjectResult;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;
use Zenstruck\Collection\Tests\PagintableCollectionTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ObjectResultTest extends TestCase
{
    use HasDatabase, PagintableCollectionTests;

    protected function createWithItems(int $count): Collection
    {
        $this->persistEntities($count);

        return new ObjectResult(
            fn(array $data) => new Entity($data['value'], $data['id']),
            $this->em->getConnection()->createQueryBuilder()->select('*')->from(Entity::TABLE, 'e')
        );
    }

    protected function expectedValueAt(int $position): Entity
    {
        return new Entity("value {$position}", $position);
    }
}
