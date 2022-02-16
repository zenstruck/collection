<?php

namespace Zenstruck\Collection\Doctrine\ORM\Specification;

use Doctrine\ORM\QueryBuilder;
use Zenstruck\Collection\Doctrine\ORM\Specification\Normalizer\AntiJoinNormalizer;
use Zenstruck\Collection\Doctrine\ORM\Specification\Normalizer\JoinNormalizer;
use Zenstruck\Collection\Doctrine\Specification\Context;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ORMContext extends Context
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

    protected static function defaultNormalizers(): array
    {
        return \array_merge(parent::defaultNormalizers(), [
            new JoinNormalizer(),
            new AntiJoinNormalizer(),
        ]);
    }
}
