<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine;

use Doctrine\Common\Collections\Criteria;
use Zenstruck\Collection;
use Zenstruck\Collection\Matchable;

/**
 * Represents a Doctrine result set.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 * @template V
 * @extends Collection<int,V>
 * @extends Matchable<int,V>
 */
interface Result extends Collection, Matchable
{
    /**
     * "Batch iterate" the result set, clearing the
     * Object Manager after each "chunk".
     *
     * @return \Traversable<V>
     */
    public function batchIterate(int $chunkSize = 100): \Traversable;

    /**
     * "Batch process" the result set, flushing and clearing
     * the Object Manager after each "chunk".
     *
     * @return \Traversable<V>
     */
    public function batchProcess(int $chunkSize = 100): \Traversable;

    /**
     * @param mixed|Criteria|callable(V,int):bool $specification
     */
    public function find(mixed $specification, mixed $default = null): mixed;

    /**
     * @param mixed|Criteria|callable(V,int):bool $specification
     */
    public function filter(mixed $specification): Collection;

    /**
     * If results are managed objects, detach them from the
     * Object Manager immediately after hydrating.
     *
     * @return self<V>
     */
    public function readonly(): self;

    /**
     * @return self<scalar>
     */
    public function asScalar(?string $field = null): self;

    /**
     * @return self<string>
     */
    public function asString(?string $field = null): self;

    /**
     * @return self<int>
     */
    public function asInt(?string $field = null): self;

    /**
     * @return self<float>
     */
    public function asFloat(?string $field = null): self;

    /**
     * @return self<array<string,mixed>>
     */
    public function asArray(string ...$fields): self;

    /**
     * @template R
     *
     * @param callable(mixed):R $modifier
     *
     * @return self<R>
     */
    public function as(callable $modifier): self;
}
