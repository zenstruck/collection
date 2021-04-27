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
    public function normalize($specification, $context): void
    {
        $context->qb()->addOrderBy($context->prefixAlias($specification->field()), $specification->direction());
    }

    protected function supportsSpecification($specification): bool
    {
        return $specification instanceof OrderBy;
    }
}
