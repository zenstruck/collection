<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Specification\Logic;

use Zenstruck\Collection\Specification\Util;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Composite implements \Stringable
{
    /** @var mixed[] */
    private array $children;

    public function __construct(mixed ...$children)
    {
        $this->children = $children;
    }

    final public function __toString(): string
    {
        $children = \array_filter(\array_map([Util::class, 'stringify'], $this->children()));

        return \sprintf('%s(%s)', (new \ReflectionClass($this))->getShortName(), \implode(', ', $children));
    }

    /**
     * @return mixed[]
     */
    final public function children(): array
    {
        return $this->children;
    }
}
