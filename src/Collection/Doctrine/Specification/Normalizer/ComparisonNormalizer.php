<?php

namespace Zenstruck\Collection\Doctrine\Specification\Normalizer;

use Doctrine\DBAL\Connection;
use Zenstruck\Collection\Doctrine\Specification\Context;
use Zenstruck\Collection\Specification\Filter\Comparison;
use Zenstruck\Collection\Specification\Filter\Equal;
use Zenstruck\Collection\Specification\Filter\GreaterThan;
use Zenstruck\Collection\Specification\Filter\GreaterThanOrEqual;
use Zenstruck\Collection\Specification\Filter\In;
use Zenstruck\Collection\Specification\Filter\LessThan;
use Zenstruck\Collection\Specification\Filter\LessThanOrEqual;
use Zenstruck\Collection\Specification\Filter\Like;
use Zenstruck\Collection\Specification\Filter\NotEqual;
use Zenstruck\Collection\Specification\Filter\NotIn;
use Zenstruck\Collection\Specification\Filter\NotLike;
use Zenstruck\Collection\Specification\Normalizer\ClassMethodMap;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ComparisonNormalizer extends DoctrineNormalizer
{
    use ClassMethodMap;

    /**
     * @param Comparison $specification
     * @param Context    $context
     */
    public function normalize($specification, $context): string
    {
        $parameter = \sprintf('comparison_%d', \count($context->qb()->getParameters()));

        $context->qb()->setParameter(
            $parameter,
            $specification->value(),
            \is_array($specification->value()) ? Connection::PARAM_STR_ARRAY : null
        );

        return $context->qb()->expr()->{self::methodFor($specification)}(
            $context->prefixAlias($specification->field()),
            ":{$parameter}"
        );
    }

    /**
     * @param Comparison $specification
     */
    public function stringify(mixed $specification, mixed $context): string
    {
        return \sprintf('Compare(%s %s %s)',
            $specification->field(),
            self::methodFor($specification),
            \is_scalar($value = $specification->value()) ? $value : \get_debug_type($value)
        );
    }

    /**
     * @return array<class-string, string>
     */
    protected static function classMethodMap(): array
    {
        return [
            Equal::class => 'eq',
            NotEqual::class => 'neq',
            GreaterThan::class => 'gt',
            GreaterThanOrEqual::class => 'gte',
            LessThan::class => 'lt',
            LessThanOrEqual::class => 'lte',
            Like::class => 'like',
            NotLike::class => 'notLike',
            In::class => 'in',
            NotIn::class => 'notIn',
        ];
    }
}
