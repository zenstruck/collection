<?php

namespace Zenstruck\Collection\Doctrine\ORM\Specification\Normalizer;

use Zenstruck\Collection\Doctrine\ORM\Specification\Join;
use Zenstruck\Collection\Doctrine\ORM\Specification\ORMContext;
use Zenstruck\Collection\Specification\Normalizer;
use Zenstruck\Collection\Specification\Normalizer\HasNormalizer;
use Zenstruck\Collection\Specification\Normalizer\NormalizerAware;
use Zenstruck\Collection\Specification\Normalizer\SplitSupports;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class JoinNormalizer implements Normalizer, NormalizerAware
{
    use HasNormalizer, SplitSupports;

    /**
     * @param Join       $join
     * @param ORMContext $context
     */
    public function normalize($join, $context)
    {
        $this->addJoin($context, $join);

        if ($join->isEager()) {
            $context->qb()->addSelect($join->alias());
        }

        if (null === $join->child()) {
            return null;
        }

        return $this->normalizer()->normalize($join->child(), $context->scopeTo($join->alias()));
    }

    protected function supportsSpecification($specification): bool
    {
        return $specification instanceof Join;
    }

    protected function supportsContext($context): bool
    {
        return $context instanceof ORMContext;
    }

    private function addJoin(ORMContext $context, Join $join): void
    {
        $joinString = $context->prefixAlias($join->field());

        foreach ($context->qb()->getDQLParts()['join'] as $entry) {
            foreach ($entry as $item) {
                if ($joinString === $item->getJoin()) {
                    return;
                }
            }
        }

        $context->qb()->{$join->type().'Join'}($joinString, $join->alias());
    }
}
