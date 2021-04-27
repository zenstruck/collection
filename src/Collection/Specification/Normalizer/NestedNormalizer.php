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
     * @param mixed  $context
     */
    public function normalize($specification, $context)
    {
        return $this->normalizer()->normalize($specification->child(), $context);
    }

    public function supports($specification, $context): bool
    {
        return $specification instanceof Nested;
    }
}
