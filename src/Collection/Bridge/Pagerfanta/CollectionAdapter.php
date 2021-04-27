<?php

namespace Zenstruck\Collection\Bridge\Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;
use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CollectionAdapter implements AdapterInterface
{
    private Collection $collection;

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
