<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Specification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class Util
{
    public static function stringify(mixed $specification): string
    {
        if ($specification instanceof \Stringable) {
            return $specification;
        }

        if ($specification instanceof Nested) {
            return \sprintf('%s(%s)', $specification::class, self::stringify($specification->specification()));
        }

        return \get_debug_type($specification);
    }
}
