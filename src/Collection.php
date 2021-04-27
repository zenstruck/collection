<?php

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Collection extends \IteratorAggregate, \Countable
{
    public function take(int $limit, int $offset = 0): self;
}
