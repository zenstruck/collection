<?php

namespace Zenstruck\Collection\Bridge;

use Pagerfanta\Adapter\AdapterInterface;
use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K
 * @template V
 * @implements AdapterInterface<V>
 */
final class PagerfantaAdapter implements AdapterInterface
{
    /** @var Collection<K,V> */
    private Collection $collection;

    /**
     * @param Collection<K,V> $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function getNbResults(): int
    {
        return $this->collection->count();
    }

    public function getSlice($offset, $length): iterable
    {
        return $this->collection->take($length, $offset);
    }
}
