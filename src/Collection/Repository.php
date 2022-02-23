<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K
 * @template V
 * @extends \IteratorAggregate<K,V>
 */
interface Repository extends \Countable, \IteratorAggregate
{
    /**
     * @return V
     *
     * @throws \RuntimeException if not found
     */
    public function get(mixed $id): mixed;

    /**
     * @return Collection<K,V>
     */
    public function filter(mixed $criteria): Collection;
}
