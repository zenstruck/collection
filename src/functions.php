<?php

namespace Zenstruck;

use Zenstruck\Collection\ArrayCollection;
use Zenstruck\Collection\LazyCollection;

/**
 * @template K of array-key
 * @template V
 *
 * @param null|iterable<K,V>|callable():iterable<K,V> $source
 *
 * @return LazyCollection<K,V>
 */
function collect(iterable|callable|null $source = null): LazyCollection
{
    return new LazyCollection($source);
}

/**
 * @template K of array-key
 * @template V
 *
 * @param null|iterable<K,V>|callable():iterable<K,V> $source
 *
 * @return ArrayCollection<K,V>
 */
function map(iterable|callable|null $source = null): ArrayCollection
{
    return new ArrayCollection($source);
}
