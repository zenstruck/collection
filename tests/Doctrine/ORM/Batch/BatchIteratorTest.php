<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Batch;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\Batch\BatchIterator;
use Zenstruck\Collection\Doctrine\ORM\Batch\CountableBatchIterator;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class BatchIteratorTest extends TestCase
{
    use HasDatabase;

    /**
     * @test
     */
    public function detaches_entities_from_em_on_iterate(): void
    {
        $this->persistEntities(2);
        $iterator = BatchIterator::for($this->em->getRepository(Entity::class)->findAll(), $this->em, 1);

        $result = \iterator_to_array($iterator)[0];

        $this->assertInstanceOf(Entity::class, $result);
        $this->assertFalse($this->em->contains($result));
    }

    /**
     * @test
     */
    public function countable_iterator(): void
    {
        $this->assertCount(3, BatchIterator::for([1, 2, 3], $this->em));
    }

    /**
     * @test
     */
    public function for_returns_the_proper_iterator(): void
    {
        $this->assertTrue(\is_countable(BatchIterator::for(['foo'], $this->em)));
        $this->assertFalse(\is_countable(BatchIterator::for((static function() { yield 1; })(), $this->em)));
        $this->assertTrue(\is_countable(CountableBatchIterator::for(['foo'], $this->em)));
        $this->assertFalse(\is_countable(CountableBatchIterator::for((static function() { yield 1; })(), $this->em)));
    }
}
