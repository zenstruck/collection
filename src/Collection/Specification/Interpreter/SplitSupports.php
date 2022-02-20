<?php

namespace Zenstruck\Collection\Specification\Interpreter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait SplitSupports
{
    public function supports(mixed $specification, mixed $context): bool
    {
        return $this->supportsSpecification($specification) && $this->supportsContext($context);
    }

    abstract protected function supportsSpecification(mixed $specification): bool;

    abstract protected function supportsContext(mixed $context): bool;
}
