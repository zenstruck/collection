<?php

namespace Zenstruck\Collection\Specification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Normalizer
{
    /**
     * @param mixed $specification
     * @param mixed $context
     *
     * @return mixed
     */
    public function normalize($specification, $context);

    /**
     * @param mixed $specification
     * @param mixed $context
     */
    public function supports($specification, $context): bool;
}
