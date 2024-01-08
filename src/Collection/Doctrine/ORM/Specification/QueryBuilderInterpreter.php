<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\ORM\Specification;

use Doctrine\ORM\Query\Expr\Comparison as DoctrineComparison;
use Doctrine\ORM\Query\Expr\Composite as DoctrineComposite;
use Doctrine\ORM\Query\Expr\Func;
use Zenstruck\Collection\Doctrine\ORM\EntityResultQueryBuilder;
use Zenstruck\Collection\Doctrine\Specification\Cache;
use Zenstruck\Collection\Doctrine\Specification\Delete;
use Zenstruck\Collection\Doctrine\Specification\Instance;
use Zenstruck\Collection\Doctrine\Specification\Unwritable;
use Zenstruck\Collection\Exception\InvalidSpecification;
use Zenstruck\Collection\Specification\Callback;
use Zenstruck\Collection\Specification\Comparison;
use Zenstruck\Collection\Specification\Filter\Contains;
use Zenstruck\Collection\Specification\Filter\EndsWith;
use Zenstruck\Collection\Specification\Filter\EqualTo;
use Zenstruck\Collection\Specification\Filter\GreaterThan;
use Zenstruck\Collection\Specification\Filter\GreaterThanOrEqualTo;
use Zenstruck\Collection\Specification\Filter\In;
use Zenstruck\Collection\Specification\Filter\IsNull;
use Zenstruck\Collection\Specification\Filter\LessThan;
use Zenstruck\Collection\Specification\Filter\LessThanOrEqualTo;
use Zenstruck\Collection\Specification\Filter\StartsWith;
use Zenstruck\Collection\Specification\Logic\AndX;
use Zenstruck\Collection\Specification\Logic\Composite;
use Zenstruck\Collection\Specification\Logic\Not;
use Zenstruck\Collection\Specification\Logic\OrX;
use Zenstruck\Collection\Specification\Nested;
use Zenstruck\Collection\Specification\OrderBy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class QueryBuilderInterpreter
{
    /**
     * @param EntityResultQueryBuilder<object> $qb
     * @param class-string                     $callingClass
     */
    private function __construct(
        private EntityResultQueryBuilder $qb,
        private string $alias,
        private string $callingClass,
        private string $callingMethod,
    ) {
    }

    /**
     * @param class-string                     $callingClass
     * @param EntityResultQueryBuilder<object> $qb
     *
     * @return EntityResultQueryBuilder<object>
     */
    public static function interpret(
        object $specification,
        string $callingClass,
        string $callingMethod,
        EntityResultQueryBuilder $qb,
        string $alias,
    ): EntityResultQueryBuilder {
        $self = new self($qb, $alias, $callingClass, $callingMethod);

        if (self::isExpression($expression = $self->transform($specification))) {
            $qb->andWhere($expression);
        }

        return $qb;
    }

    private function transform(object $specification): mixed
    {
        if ($specification instanceof Nested) {
            return $this->transform($specification->specification());
        }

        return match ($specification::class) {
            AndX::class => $this->composite($specification, 'andX'),
            OrX::class => $this->composite($specification, 'orX'),
            Not::class => $this->composite($specification, 'not'),

            EqualTo::class => $this->qb->expr()->eq($this->prefix($specification->field()), $this->param($specification->value())),
            LessThan::class => $this->qb->expr()->lt($this->prefix($specification->field()), $this->param($specification->value())),
            LessThanOrEqualTo::class => $this->qb->expr()->lte($this->prefix($specification->field()), $this->param($specification->value())),
            GreaterThan::class => $this->qb->expr()->gt($this->prefix($specification->field()), $this->param($specification->value())),
            GreaterThanOrEqualTo::class => $this->qb->expr()->gte($this->prefix($specification->field()), $this->param($specification->value())),
            In::class => $this->qb->expr()->in($this->prefix($specification->field()), $this->param($specification->value())),
            IsNull::class => $this->qb->expr()->isNull($this->prefix($specification->field())),
            Contains::class => $this->qb->expr()->like($this->prefix($specification->field()), $this->param('%'.self::normalizeLike($specification).'%')),
            StartsWith::class => $this->qb->expr()->like($this->prefix($specification->field()), $this->param(self::normalizeLike($specification).'%')),
            EndsWith::class => $this->qb->expr()->like($this->prefix($specification->field()), $this->param('%'.self::normalizeLike($specification))),

            Callback::class => $specification->value()($this->qb, $this->alias), // @phpstan-ignore-line

            OrderBy::class => $this->qb->addOrderBy($this->prefix($specification->field()), $specification->direction()),

            Instance::class => $this->qb->expr()->isInstanceOf($this->alias, $this->param($specification->of())),
            Delete::class => $this->qb->delete(),
            Unwritable::class => $this->qb->readonly(),
            Cache::class => $this->qb->cacheResult($specification->lifetime(), $specification->key()),
            AntiJoin::class => $this->qb->leftJoin($this->prefix($specification->field()), $specification->field())->andWhere($this->qb->expr()->isNull($specification->field())),
            Join::class => $this->interpretJoin($specification),

            default => throw InvalidSpecification::build($specification, $this->callingClass, $this->callingMethod),
        };
    }

    private function interpretJoin(Join $join): mixed
    {
        $this->addJoinToQueryBuilder($join);

        if ($join->isEager()) {
            $this->qb->addSelect($join->alias());
        }

        if (null === $join->child()) {
            return null;
        }

        $interpreter = clone $this;
        $interpreter->alias = $join->alias();

        return $interpreter->transform($join->child());
    }

    private function addJoinToQueryBuilder(Join $join): void
    {
        $field = $this->prefix($join->field());

        foreach ($this->qb->getDQLParts()['join'] as $entry) {
            foreach ($entry as $item) {
                if ($field === $item->getJoin()) {
                    // join already added
                    return;
                }
            }
        }

        $this->qb->{$join->type().'Join'}($field, $join->alias());
    }

    private function composite(Composite $specification, string $method): DoctrineComposite|Func|null
    {
        if (!$expressions = $this->filter($specification)) {
            return null;
        }

        return $this->qb->expr()->{$method}(...$expressions);
    }

    /**
     * @return list<Func|DoctrineComparison|DoctrineComposite>
     */
    private function filter(Composite $specification): array
    {
        return \array_values(
            \array_filter(
                \array_map(
                    fn(object $child) => $this->transform($child),
                    $specification->children(),
                ),
                static fn(mixed $child) => self::isExpression($child),
            ),
        );
    }

    private static function normalizeLike(Comparison $comparison): string
    {
        return \str_replace(['%', '*'], ['%%', '%'], \trim($comparison->value(), '*')); // todo make wildcard char configurable?
    }

    private function param(mixed $value): string
    {
        $param = \sprintf('param_%d', \count($this->qb->getParameters()) + 1);

        $this->qb->setParameter($param, $value);

        return ":{$param}";
    }

    private static function isExpression(mixed $what): bool
    {
        return \is_string($what) || $what instanceof Func || $what instanceof DoctrineComparison || $what instanceof DoctrineComposite;
    }

    private function prefix(string $field): string
    {
        return "{$this->alias}.{$field}";
    }
}
