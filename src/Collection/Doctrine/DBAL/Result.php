<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Zenstruck\Collection;
use Zenstruck\Collection\FactoryCollection;
use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\LazyCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @implements Collection<int,V>
 */
class Result implements Collection
{
    /** @use IterableCollection<int,V> */
    use IterableCollection;

    private QueryBuilder $qb;

    /** @var callable(QueryBuilder):QueryBuilder */
    private $countModifier;

    /** @var null|callable(array<string,mixed>):V */
    private $resultFactory;

    private ?int $count = null;

    /**
     * @param null|callable(QueryBuilder):QueryBuilder $countModifier
     * @param null|callable(array<string,mixed>):V     $resultFactory
     */
    public function __construct(QueryBuilder $qb, ?callable $countModifier = null, ?callable $resultFactory = null)
    {
        $this->qb = $qb;
        $this->countModifier = $countModifier ?? static fn(QueryBuilder $qb): QueryBuilder => $qb->select('COUNT(*)');
        $this->resultFactory = $resultFactory;
    }

    public function take(int $limit, int $offset = 0): Collection
    {
        $collection = new LazyCollection(
            fn() => (clone $this->qb)->setFirstResult($offset)->setMaxResults($limit)->{self::executeMethod()}()->fetchAllAssociative()
        );

        if (!$this->resultFactory) {
            return $collection;
        }

        return new FactoryCollection($collection, $this->resultFactory);
    }

    public function count(): int
    {
        return $this->count ??= ($this->countModifier)(clone $this->qb)->{self::executeMethod()}()->fetchOne();
    }

    public function getIterator(): \Traversable
    {
        $collection = new LazyCollection(function() {
            $stmt = $this->qb->{self::executeMethod()}();

            while ($data = $stmt->fetchAssociative()) {
                yield $data;
            }
        });

        if (!$this->resultFactory) {
            return $collection;
        }

        return new FactoryCollection($collection, $this->resultFactory);
    }

    private static function executeMethod(): string
    {
        return \method_exists(QueryBuilder::class, 'executeQuery') ? 'executeQuery' : 'execute';
    }
}
