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
    public function normalize(mixed $specification, mixed $context): mixed
    {
        return $specification($context);
    }

    protected function supportsSpecification(mixed $specification): bool
    {
        return \is_callable($specification);
    }
}
