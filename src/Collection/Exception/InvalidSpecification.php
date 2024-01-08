<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Exception;

use Zenstruck\Collection\Specification\Util;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InvalidSpecification extends \InvalidArgumentException
{
    /**
     * @param class-string $class
     */
    public static function build(mixed $what, string $class, string $method, string $message = ''): self
    {
        if (\is_scalar($what)) {
            $what = \sprintf('%s (%s)', $what, \get_debug_type($what));
        }

        if (\is_object($what) && !\is_callable($what)) {
            $what = Util::stringify($what);
        }

        if (!\is_scalar($what)) {
            $what = \get_debug_type($what);
        }

        if ('' !== $message) {
            $message = ' '.$message;
        }

        return new self(\sprintf('"%s::%s()" does not support specification "%s".%s', $class, $method, $what, $message));
    }
}
