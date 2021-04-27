<?php

namespace Zenstruck\Collection\Doctrine\Specification;

use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use Zenstruck\Collection\Doctrine\Specification\Normalizer\CallableNormalizer;
use Zenstruck\Collection\Doctrine\Specification\Normalizer\ComparisonNormalizer;
use Zenstruck\Collection\Doctrine\Specification\Normalizer\CompositeNormalizer;
use Zenstruck\Collection\Doctrine\Specification\Normalizer\NullNormalizer;
use Zenstruck\Collection\Doctrine\Specification\Normalizer\OrderByNormalizer;
use Zenstruck\Collection\Specification\Normalizer\NestedNormalizer;
use Zenstruck\Collection\Specification\SpecificationNormalizer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Context
{
    private static array $defaultNormalizer = [];

    private string $alias;

    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    final public function alias(): string
    {
        return $this->alias;
    }

    final public function prefixAlias(string $value): string
    {
        return "{$this->alias}.{$value}";
    }

    /**
     * @return ORMQueryBuilder|DBALQueryBuilder
     */
    abstract public function qb(): object;

    /**
     * @return static
     */
    abstract public function scopeTo(string $alias): self;

    final public static function defaultNormalizer(): SpecificationNormalizer
    {
        return self::$defaultNormalizer[static::class] ??= new SpecificationNormalizer(static::defaultNormalizers());
    }

    protected static function defaultNormalizers(): array
    {
        return [
            new NestedNormalizer(),
            new CallableNormalizer(),
            new ComparisonNormalizer(),
            new CompositeNormalizer(),
            new NullNormalizer(),
            new OrderByNormalizer(),
        ];
    }
}
