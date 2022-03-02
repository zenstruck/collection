<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use Composer\InstalledVersions;
use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORMResult;
use Zenstruck\Collection\Tests\CollectionTests;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;
use Zenstruck\Collection\Tests\PagintableCollectionTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class QueryExtraFieldsResultTest extends TestCase
{
    use CollectionTests, HasDatabase, PagintableCollectionTests;

    protected function setUp(): void
    {
        // see https://github.com/doctrine/orm/pull/8467 & https://github.com/doctrine/orm/issues/8520 for more details
        // leaving the code here in case there is a future fix

        if (\version_compare(InstalledVersions::getVersion('doctrine/orm'), '2.8.2', '>=')) {
            $this->markTestSkipped('Iterating "mixed" results (results with entity and scalar) is no longer supported as of doctrine/orm 2.8.2.');
        }
    }

    /**
     * @test
     */
    public function detaches_entity_from_em_on_batch_iterate(): void
    {
        $result = \iterator_to_array($this->createWithItems(2)->batch())[0][0];

        $this->assertFalse($this->em->contains($result));
    }

    /**
     * @test
     */
    public function can_batch_update_results(): void
    {
        $result = $this->createWithItems(2);
        $values = \array_map(static fn(array $row) => $row[0]->value, \iterator_to_array($result));

        $this->assertSame(['value 1', 'value 2'], $values);

        $batchIterator = $result->batchProcess();

        $this->assertCount(2, $batchIterator);

        foreach ($batchIterator as $row) {
            $row[0]->value = 'new '.$row[0]->value;
        }

        $values = \array_map(
            static fn(Entity $entity) => $entity->value,
            $this->em->getRepository(Entity::class)->findAll()
        );

        $this->assertSame(['new value 1', 'new value 2'], $values);
    }

    /**
     * @test
     */
    public function can_batch_delete_results(): void
    {
        $result = $this->createWithItems(2);

        $this->assertCount(2, $result);

        $batchIterator = $result->batchProcess();

        $this->assertCount(2, $batchIterator);

        foreach ($batchIterator as $item) {
            $this->em->remove($item[0]);
        }

        $this->assertCount(0, $this->em->getRepository(Entity::class)->findAll());
    }

    protected function createWithItems(int $count): ORMResult
    {
        $this->persistEntities($count);

        $query = $this->em->createQuery(\sprintf('SELECT e, UPPER(e.value) AS extra FROM %s e', Entity::class));

        return new ORMResult($query, true, false);
    }

    protected function expectedValueAt(int $position): array
    {
        $value = 'value '.$position;

        return [
            0 => new Entity($value, $position),
            'extra' => \mb_strtoupper($value),
        ];
    }
}
