<?php

namespace Zenstruck\Collection\Doctrine\ORM\Specification\Normalizer;

use Zenstruck\Collection\Doctrine\ORM\Specification\AntiJoin;
use Zenstruck\Collection\Doctrine\ORM\Specification\ORMContext;
use Zenstruck\Collection\Specification\Normalizer;
use Zenstruck\Collection\Specification\Normalizer\SplitSupports;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AntiJoinNormalizer implements Normalizer
{
    use SplitSupports;

    /**
     * @param AntiJoin   $specification
     * @param ORMContext $context
     */
    public function normalize(mixed $specification, mixed $context): mixed
    {
        $context->qb()
            ->leftJoin($context->prefixAlias($specification->field()), $specification->field())
            ->andWhere("{$specification->field()} IS NULL")
        ;

        return null;
    }

    /**
     * @param AntiJoin $specification
     */
    public function stringify(mixed $specification, mixed $context): string
    {
        return \sprintf('AntiJoin(%s)', $specification->field());
    }

    protected function supportsSpecification(mixed $specification): bool
    {
        return $specification instanceof AntiJoin;
    }

    protected function supportsContext(mixed $context): bool
    {
        return $context instanceof ORMContext;
    }
}
