<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K
 * @template V
 */
interface Filterable
{
    /**
     * Filter results that match the "specification".
     *
     * @return Collection<K,V>
     */
    public function filter(mixed $specification): Collection;

    /**
     * Return the first result that matches the "specification".
     *
     * @return V
     *
     * @throws \RuntimeException If no match found
     */
    public function get(mixed $specification): mixed;
}
