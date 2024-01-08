<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\Specification;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Zenstruck\Collection\Exception\InvalidSpecification;
use Zenstruck\Collection\Specification\Callback;
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
final class CriteriaInterpreter
{
    /** @var array<string,string> */
    private array $orderBy = [];

    /**
     * @param class-string $class
     */
    private function __construct(private Criteria $criteria, private string $class, private string $method)
    {
    }

    /**
     * @param class-string $class
     */
    public static function interpret(object $specification, string $class, string $method, ?Criteria $criteria = null): Criteria
    {
        $self = new self($criteria ?? new Criteria(), $class, $method);

        if ($expression = $self->transform($specification)) {
            $self->criteria->andWhere($expression);
        }

        if ($self->orderBy) {
            $self->criteria->orderBy($self->orderBy);
        }

        return $self->criteria;
    }

    private function transform(object $specification): ?Expression
    {
        if ($specification instanceof Nested) {
            return $this->transform($specification->specification());
        }

        if ($specification instanceof OrderBy) {
            $this->orderBy[$specification->field()] = $specification->direction();

            return null;
        }

        return match ($specification::class) {
            AndX::class => $this->composite($specification, 'andX'),
            OrX::class => $this->composite($specification, 'orX'),
            Not::class => $this->composite($specification, 'not'),

            Contains::class => Criteria::expr()->contains($specification->field(), $specification->value()),
            EndsWith::class => Criteria::expr()->endsWith($specification->field(), $specification->value()),
            EqualTo::class => Criteria::expr()->eq($specification->field(), $specification->value()),
            GreaterThan::class => Criteria::expr()->gt($specification->field(), $specification->value()),
            GreaterThanOrEqualTo::class => Criteria::expr()->gte($specification->field(), $specification->value()),
            In::class => Criteria::expr()->in($specification->field(), $specification->value()),
            IsNull::class => Criteria::expr()->isNull($specification->field()),
            LessThan::class => Criteria::expr()->lt($specification->field(), $specification->value()),
            LessThanOrEqualTo::class => Criteria::expr()->lte($specification->field(), $specification->value()),
            StartsWith::class => Criteria::expr()->startsWith($specification->field(), $specification->value()),

            Callback::class => $specification->value()($this->criteria),

            default => throw InvalidSpecification::build($specification, $this->class, $this->method),
        };
    }

    private function composite(Composite $specification, string $method): ?Expression
    {
        if (!$expressions = $this->filter($specification)) {
            return null;
        }

        return Criteria::expr()->{$method}(...$expressions);
    }

    /**
     * @return Expression[]
     */
    private function filter(Composite $specification): array
    {
        return \array_values(
            \array_filter(
                \array_map(
                    fn(object $child) => $this->transform($child),
                    $specification->children(),
                ),
                static fn(mixed $child) => $child instanceof Expression,
            ),
        );
    }
}
