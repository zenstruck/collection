<?php

namespace Zenstruck\Collection\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;
use Zenstruck\Collection;
use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Result implements Collection
{
    use Paginatable;

    private QueryBuilder $qb;
    private $countModifier;
    private ?int $count = null;

    public function __construct(QueryBuilder $qb, ?callable $countModifier = null)
    {
        $this->qb = $qb;
        $this->countModifier = $countModifier ?? static fn(QueryBuilder $qb): QueryBuilder => $qb->select('COUNT(*)');
    }

    public function take(int $limit, int $offset = 0): Collection
    {
        return new IterableCollection(
            fn() => (clone $this->qb)->setFirstResult($offset)->setMaxResults($limit)->execute()->fetchAll()
        );
    }

    public function count(): int
    {
        return $this->count ??= ($this->countModifier)(clone $this->qb)->execute()->fetchColumn();
    }

    public function getIterator(): \Traversable
    {
        $stmt = $this->qb->execute();

        while ($data = $stmt->fetch()) {
            yield $data;
        }
    }
}
