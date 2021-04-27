<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;
use Zenstruck\Collection\Exception\NotFound;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Matchable
{
    /**
     * @param mixed $specification
     */
    public function match($specification): Collection;

    /**
     * @param mixed $specification
     *
     * @return mixed
     *
     * @throws NotFound If no match found
     */
    public function matchOne($specification);
}
