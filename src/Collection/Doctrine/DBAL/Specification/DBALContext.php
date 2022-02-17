<?php

namespace Zenstruck\Collection\Doctrine\DBAL\Specification;

use Doctrine\DBAL\Query\QueryBuilder;
use Zenstruck\Collection\Doctrine\Specification\Context;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DBALContext extends Context
{
    private QueryBuilder $qb;

    public function __construct(QueryBuilder $qb, string $alias)
    {
        parent::__construct($alias);

        $this->qb = $qb;
    }

    public function qb(): QueryBuilder
    {
        return $this->qb;
    }

    public function scopeTo(string $alias): static
    {
        return new self($this->qb, $alias);
    }
}
