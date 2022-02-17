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
    public function normalize(mixed $specification, mixed $context): mixed
    {
        $children = \array_filter(\array_map(
            fn($child) => $this->normalizer()->normalize($child, $context),
            $specification->children()
        ));

        if (empty($children)) {
            return null;
        }

        return $context->qb()->expr()->{self::methodFor($specification)}(...$children);
    }

    /**
     * @param Composite $specification
     */
    public function stringify(mixed $specification, mixed $context): string
    {
        $children = \array_filter(\array_map(
            fn($child) => $this->normalizer()->stringify($child, $context),
            $specification->children()
        ));

        return \sprintf('%s(%s)', self::methodFor($specification), \implode(', ', $children));
    }

    /**
     * @return array<class-string, string>
     */
    protected static function classMethodMap(): array
    {
        return [
            AndX::class => 'andX',
            OrX::class => 'orX',
        ];
    }
}
