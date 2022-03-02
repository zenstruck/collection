<?php

namespace Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 * @extends Repository<K,V>
 */
interface Store extends Repository
{
    public function add(mixed $item): static;

    public function remove(mixed $item): static;
}
