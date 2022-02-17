<?php

namespace Zenstruck\Collection\Doctrine\Specification\Normalizer;

use Zenstruck\Collection\Doctrine\Specification\Context;
use Zenstruck\Collection\Specification\OrderBy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class OrderByNormalizer extends DoctrineNormalizer
{
    /**
     * @param OrderBy $specification
     * @param Context $context
     */
    public function normalize(mixed $specification, mixed $context): mixed
    {
        $context->qb()->addOrderBy($context->prefixAlias($specification->field()), $specification->direction());

        return null;
    }

    /**
     * @param OrderBy $specification
     */
    public function stringify(mixed $specification, mixed $context): string
    {
        return \sprintf('OrderBy%s(%s)', $specification->direction(), $specification->field());
    }

    protected function supportsSpecification(mixed $specification): bool
    {
        return $specification instanceof OrderBy;
    }
}
