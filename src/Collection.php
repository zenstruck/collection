<?php

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Key
 * @template Value
 * @extends \IteratorAggregate<Key,Value>
 */
interface Collection extends \IteratorAggregate, \Countable
{
    /**
     * @return self<Key,Value>
     */
    public function take(int $limit, int $offset = 0): self;
}
