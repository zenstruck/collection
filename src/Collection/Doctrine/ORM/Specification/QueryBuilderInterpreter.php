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
use Zenstruck\Collection\Specification\Filter\Between;
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
    private const LIKE_ESCAPE = '!';

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

            EqualTo::class => $this->qb->expr()->eq($this->prefix($specification->field), $this->param($specification->value)),
            LessThan::class => $this->qb->expr()->lt($this->prefix($specification->field), $this->param($specification->value)),
            LessThanOrEqualTo::class => $this->qb->expr()->lte($this->prefix($specification->field), $this->param($specification->value)),
            GreaterThan::class => $this->qb->expr()->gt($this->prefix($specification->field), $this->param($specification->value)),
            GreaterThanOrEqualTo::class => $this->qb->expr()->gte($this->prefix($specification->field), $this->param($specification->value)),
            In::class => $this->qb->expr()->in($this->prefix($specification->field), $this->param($specification->value)),
            IsNull::class => $this->qb->expr()->isNull($this->prefix($specification->field)),
            Contains::class => $this->like($specification, '%', '%'),
            StartsWith::class => $this->like($specification, '', '%'),
            EndsWith::class => $this->like($specification, '%', ''),
            Between::class => $this->interpretBetween($specification),

            Callback::class => ($specification->value)($this->qb, $this->alias),

            OrderBy::class => $this->qb->addOrderBy($this->prefix($specification->field), $specification->direction),

            Instance::class => $this->qb->expr()->isInstanceOf($this->alias, $this->param($specification->of())),
            Delete::class => $this->qb->delete(),
            Unwritable::class => $this->qb->readonly(),
            Cache::class => $this->qb->cacheResult($specification->lifetime(), $specification->key()),
            AntiJoin::class => $this->interpretAntiJoin($specification),
            Join::class => $this->interpretJoin($specification),

            default => throw InvalidSpecification::build($specification, $this->callingClass, $this->callingMethod),
        };
    }

    private function interpretBetween(Between $between): mixed
    {
        if (Between::INCLUSIVE === $between->type) {
            return $this->qb->expr()->between(
                $this->prefix($between->field),
                $this->param($between->begin),
                $this->param($between->end),
            );
        }

        return $this->transform($between->asAnd());
    }

    private function interpretAntiJoin(AntiJoin $join): mixed
    {
        $alias = $this->joinAlias($join->field);

        $this->qb->leftJoin($this->prefix($join->field), $alias);

        return $this->qb->expr()->isNull($alias);
    }

    private function interpretJoin(Join $join): mixed
    {
        $alias = $this->addJoinToQueryBuilder($join);

        if ($join->isEager()) {
            $this->qb->addSelect($alias);
        }

        if (null === $join->child()) {
            return null;
        }

        $interpreter = clone $this;
        $interpreter->alias = $alias;

        return $interpreter->transform($join->child());
    }

    private function addJoinToQueryBuilder(Join $join): string
    {
        $field = $this->prefix($join->field);

        foreach ($this->qb->getDQLParts()['join'] as $entry) {
            foreach ($entry as $item) {
                if ($field === $item->getJoin()) {
                    // join already added - reuse its alias
                    return $item->getAlias();
                }
            }
        }

        $alias = $this->joinAlias($join->alias(), $join->hasExplicitAlias());

        $this->qb->{$join->type().'Join'}($field, $alias);

        return $alias;
    }

    /**
     * Qualifies the alias of a nested join with its parent's so that relations
     * with the same name on different parents don't collide.
     */
    private function joinAlias(string $alias, bool $explicit = false): string
    {
        if ($explicit || $this->alias === ($this->qb->getRootAliases()[0] ?? $this->alias)) {
            return $alias;
        }

        return \sprintf('%s_%s', $this->alias, $alias);
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
                    $specification->children,
                ),
                static fn(mixed $child) => self::isExpression($child),
            ),
        );
    }

    /**
     * Escapes the SQL wildcards in the value, then converts the user-facing
     * wildcard (*) into a real one. Leading/trailing wildcards are trimmed
     * as $prefix/$suffix already add them where required.
     */
    private function like(Comparison $comparison, string $prefix, string $suffix): string
    {
        $value = \str_replace(
            [self::LIKE_ESCAPE, '%', '_', '*'], // todo make wildcard char configurable?
            [self::LIKE_ESCAPE.self::LIKE_ESCAPE, self::LIKE_ESCAPE.'%', self::LIKE_ESCAPE.'_', '%'],
            \trim($comparison->value, '*'),
        );

        return \sprintf(
            '%s LIKE %s ESCAPE %s',
            $this->prefix($comparison->field),
            $this->param($prefix.$value.$suffix),
            $this->qb->expr()->literal(self::LIKE_ESCAPE),
        );
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
