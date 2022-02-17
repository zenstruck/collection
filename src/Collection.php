<?php

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K
 * @template V
 * @extends \IteratorAggregate<K,V>
 */
interface Collection extends \IteratorAggregate, \Countable
{
    /**
     * @return self<K,V>
     */
    public function take(int $limit, int $offset = 0): self;
}
