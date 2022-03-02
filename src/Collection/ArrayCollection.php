<?php

namespace Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 */
final class ArrayCollection
{
    /** @var array<K,V> */
    private array $source;

    /**
     * @param iterable<K,V>|callable():iterable<K,V>|null $source
     */
    public function __construct(iterable|callable|null $source = null)
    {
        $this->source = \is_array($source) ? $source : (new IterableCollection($source))->toArray();
    }

    /**
     * @return array<K,V>
     */
    public function all(): array
    {
        return $this->source;
    }
}
