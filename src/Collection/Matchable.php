<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection;

use Zenstruck\Collection;
use Zenstruck\Collection\Exception\InvalidSpecification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K
 * @template V
 */
interface Matchable
{
    /**
     * @return V|null
     *
     * @throws InvalidSpecification if $specification is not valid
     */
    public function find(object $specification): mixed;

    /**
     * @return Collection<K,V>
     */
    public function filter(mixed $specification): Collection;
}
