<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;
use Zenstruck\Collection\Exception\NotFound;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K
 * @template V
 */
interface Matchable
{
    /**
     * @return Collection<K,V>
     */
    public function match(mixed $specification): Collection;

    /**
     * @return V
     *
     * @throws NotFound If no match found
     */
    public function matchOne(mixed $specification): mixed;
}
