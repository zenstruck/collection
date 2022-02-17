<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;
use Zenstruck\Collection\Exception\NotFound;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Key
 * @template Value
 */
interface Matchable
{
    /**
     * @return Collection<Key,Value>
     */
    public function match(mixed $specification): Collection;

    /**
     * @return Value
     *
     * @throws NotFound If no match found
     */
    public function matchOne(mixed $specification): mixed;
}
