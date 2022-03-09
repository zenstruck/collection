<?php

namespace Zenstruck\Collection\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Zenstruck\Collection;
use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @implements Collection<int,V>
 */
class Result implements Collection
{
    /** @use Paginatable<V> */
    use Paginatable;

    private QueryBuilder $qb;

    /** @var \Closure(QueryBuilder):QueryBuilder */
    private \Closure $countModifier;
    private ?int $count = null;

    /**
     * @param null|callable(QueryBuilder):QueryBuilder $countModifier
     */
    public function __construct(QueryBuilder $qb, ?callable $countModifier = null)
    {
        $this->qb = $qb;
        $this->countModifier = \Closure::fromCallable(
            $countModifier ?? static fn(QueryBuilder $qb): QueryBuilder => $qb->select('COUNT(*)')
        );
    }

    public function take(int $limit, int $offset = 0): Collection
    {
        return new LazyCollection(
            fn() => (clone $this->qb)->setFirstResult($offset)->setMaxResults($limit)->{self::executeMethod()}()->fetchAllAssociative()
        );
    }

    public function count(): int
    {
        return $this->count ??= ($this->countModifier)(clone $this->qb)->{self::executeMethod()}()->fetchOne();
    }

    public function getIterator(): \Traversable
    {
        return new LazyCollection(function() {
            $stmt = $this->qb->{self::executeMethod()}();

            while ($data = $stmt->fetchAssociative()) {
                yield $data;
            }
        });
    }

    private static function executeMethod(): string
    {
        return \method_exists(QueryBuilder::class, 'executeQuery') ? 'executeQuery' : 'execute';
    }
}
