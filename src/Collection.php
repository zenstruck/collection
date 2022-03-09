<?php

namespace Zenstruck;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 * @extends \IteratorAggregate<K,V>
 */
interface Collection extends \IteratorAggregate, \Countable
{
    /**
     * @param callable(V,K):bool $predicate
     *
     * @return self<K,V>
     */
    public function filter(callable $predicate): self;

    /**
     * @template T
     *
     * @param callable(V,K):T $function
     *
     * @return self<K,T>
     */
    public function map(callable $function): self;

    /**
     * @template T of array-key|\Stringable
     * @template U
     *
     * @param callable(V,K):iterable<T,U> $function
     *
     * @return self<array-key,U>
     */
    public function mapWithKeys(callable $function): self;

    /**
     * @template T of array-key|\Stringable
     *
     * @param callable(V,K):T $function
     *
     * @return self<array-key,V>
     */
    public function keyBy(callable $function): self;

    /**
     * @return self<K,V>
     */
    public function take(int $limit, int $offset = 0): self;

    /**
     * @template D
     *
     * @param D $default
     *
     * @return V|D
     */
    public function first(mixed $default = null): mixed;
}
