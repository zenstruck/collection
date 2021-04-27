<?php

namespace Zenstruck\Collection\Doctrine\Specification\Normalizer;

use Zenstruck\Collection\Doctrine\Specification\Context;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallableNormalizer extends DoctrineNormalizer
{
    /**
     * @param callable $specification
     * @param Context  $context
     */
    public function normalize($specification, $context)
    {
        return $specification($context);
    }

    protected function supportsSpecification($specification): bool
    {
        return \is_callable($specification);
    }
}
