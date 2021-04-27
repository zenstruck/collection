<?php

namespace Zenstruck\Collection\Specification\Normalizer;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait SplitSupports
{
    public function supports($specification, $context): bool
    {
        return $this->supportsSpecification($specification) && $this->supportsContext($context);
    }

    abstract protected function supportsSpecification($specification): bool;

    abstract protected function supportsContext($context): bool;
}
