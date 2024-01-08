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
final class Cache implements \Stringable
{
    public function __construct(private ?int $lifetime = null, private ?string $key = null)
    {
    }

    public function __toString(): string
    {
        return \sprintf('Cache(lifetime: %s, key: %s)', $this->lifetime ?? '<null>', $this->key ?? '<null>');
    }

    public function lifetime(): ?int
    {
        return $this->lifetime;
    }

    public function key(): ?string
    {
        return $this->key;
    }
}
