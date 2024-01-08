<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\Specification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Unwritable implements \Stringable
{
    public function __toString(): string
    {
        return 'Readonly';
    }
}
