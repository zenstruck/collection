<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Grid\Filter;

use Zenstruck\Collection\ArrayCollection;
use Zenstruck\Collection\Grid\Filter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @implements \IteratorAggregate<string,Choice>
 */
final class ChoiceFilter implements Filter, \IteratorAggregate, \Countable
{
    /** @var ArrayCollection<string,Choice> */
    private ArrayCollection $choices;

    public function __construct(Choice ...$choices)
    {
        $this->choices = ArrayCollection::for($choices)->keyBy(fn(Choice $choice) => (string) $choice->value);
    }

    public function apply(mixed $value): ?object
    {
        if (!\is_string($value)) {
            return null;
        }

        return $this->choices->get($value)?->specification;
    }

    public function getIterator(): \Traversable
    {
        return $this->choices;
    }

    public function count(): int
    {
        return $this->choices->count();
    }
}
