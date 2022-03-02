<?php

namespace Zenstruck;

use Zenstruck\Collection\IterableCollection;

/**
 * @template K of array-key
 * @template V
 *
 * @param iterable<K,V>|callable():iterable<K,V>|null $source
 *
 * @return IterableCollection<K,V>
 */
function collect(iterable|callable|null $source = null): IterableCollection
{
    return new IterableCollection($source);
}
