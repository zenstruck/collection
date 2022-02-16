<?php

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Collection extends \IteratorAggregate, \Countable
{
    /**
     * @param positive-int $limit
     * @param positive-int $offset
     */
    public function take(int $limit, int $offset = 0): self;
}
