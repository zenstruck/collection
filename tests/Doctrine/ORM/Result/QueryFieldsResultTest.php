<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\ORM\ResultTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class QueryFieldsResultTest extends ResultTest
{
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

        return (new Result($query))->asArray();
    }

    protected function expectedValueAt(int $position): array
    {
        return [
            'id' => $position,
            'my_value' => 'value '.$position,
        ];
    }
}
