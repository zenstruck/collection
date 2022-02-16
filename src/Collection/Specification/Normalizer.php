<?php

namespace Zenstruck\Collection\Specification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Normalizer
{
    public function normalize(mixed $specification, mixed $context): mixed;

    public function supports(mixed $specification, mixed $context): bool;
}
