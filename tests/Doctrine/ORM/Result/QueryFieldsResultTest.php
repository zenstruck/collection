<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;
use Zenstruck\Collection\Tests\PagintableCollectionTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class QueryFieldsResultTest extends TestCase
{
    use HasDatabase, PagintableCollectionTests;

    /**
     * @test
     */
    public function can_batch_iterate(): void
    {
        $result = $this->createWithItems(2)->batch();

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

    protected function createWithItems(int $count): Result
    {
        $this->persistEntities($count);

        $query = $this->em->createQuery(\sprintf('SELECT e.id, e.value AS my_value FROM %s e', Entity::class));

        return new Result($query, true, false);
    }

    protected function expectedValueAt(int $position): array
    {
        return [
            'id' => $position,
            'my_value' => 'value '.$position,
        ];
    }
}
