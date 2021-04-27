<?php

namespace Zenstruck\Collection\Doctrine\Specification\Normalizer;

use Zenstruck\Collection\Doctrine\Specification\Context;
use Zenstruck\Collection\Specification\Logic\AndX;
use Zenstruck\Collection\Specification\Logic\Composite;
use Zenstruck\Collection\Specification\Logic\OrX;
use Zenstruck\Collection\Specification\Normalizer\ClassMethodMap;
use Zenstruck\Collection\Specification\Normalizer\HasNormalizer;
use Zenstruck\Collection\Specification\Normalizer\NormalizerAware;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CompositeNormalizer extends DoctrineNormalizer implements NormalizerAware
{
    use ClassMethodMap, HasNormalizer;

    /**
     * @param Composite $specification
     * @param Context   $context
     */
    public function normalize($specification, $context)
    {
        $children = \array_filter(\array_map(
            function($child) use ($context) {
                return $this->normalizer()->normalize($child, $context);
            },
            $specification->children()
        ));

        if (empty($children)) {
            return null;
        }

        return $context->qb()->expr()->{self::methodFor($specification)}(...$children);
    }

    protected static function classMethodMap(): array
    {
        return [
            AndX::class => 'andX',
            OrX::class => 'orX',
        ];
    }
}
