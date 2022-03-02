<?php

namespace Zenstruck\Collection\Tests\Doctrine\DBAL;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection;
use Zenstruck\Collection\Tests\CollectionTests;
use Zenstruck\Collection\Tests\Doctrine\DBAL\Fixture\Repository;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;
use Zenstruck\Collection\Tests\PagintableCollectionTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RepositoryTest extends TestCase
{
    use CollectionTests, HasDatabase, PagintableCollectionTests;

    protected function createWithItems(int $count): Collection
    {
        $this->persistEntities($count);

        return new Repository($this->em->getConnection());
    }

    protected function expectedValueAt(int $position): array
    {
        return [
            'id' => $position,
            'value' => "value {$position}",
            'relation_id' => null,
        ];
    }
}
