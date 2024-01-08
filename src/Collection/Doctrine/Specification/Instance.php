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
final class Instance implements \Stringable
{
    /**
     * @param class-string $of
     */
    public function __construct(private string $of)
    {
    }

    public function __toString(): string
    {
        return \sprintf('Instance(%s)', $this->of);
    }

    public function of(): string
    {
        return $this->of;
    }
}
