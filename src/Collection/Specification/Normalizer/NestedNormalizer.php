<?php

namespace Zenstruck\Collection\Specification\Normalizer;

use Zenstruck\Collection\Specification\Nested;
use Zenstruck\Collection\Specification\Normalizer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class NestedNormalizer implements Normalizer, NormalizerAware
{
    use HasNormalizer;

    /**
     * @param Nested $specification
     */
    public function normalize($specification, mixed $context): mixed
    {
        return $this->normalizer()->normalize($specification->child(), $context);
    }

    /**
     * @param Nested $specification
     */
    public function stringify(mixed $specification, mixed $context): string
    {
        return \sprintf('%s(%s)', $specification::class, $this->normalizer()->stringify($specification->child(), $context));
    }

    public function supports(mixed $specification, mixed $context): bool
    {
        return $specification instanceof Nested;
    }
}
