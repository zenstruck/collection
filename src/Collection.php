<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck;

use Zenstruck\Collection\ArrayCollection;
use Zenstruck\Collection\Exception\InvalidSpecification;
use Zenstruck\Collection\Page;
use Zenstruck\Collection\Pages;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @template K = array-key
 * @extends \IteratorAggregate<K,V>
 *
 * @method static dump()
 * @method never  dd()
 */
interface Collection extends \IteratorAggregate, \Countable
{
    /**
     * @param mixed|callable(V,K):bool $specification
     *
     * @return self<V,K>
     *
     * @throws InvalidSpecification if $specification is not valid
     */
    public function filter(mixed $specification): self;

    /**
     * @template T
     *
     * @param callable(V,K):T $function
     *
     * @return self<T,K>
     */
    public function map(callable $function): self;

    /**
     * @template T
     *
     * @param callable(V,K):T $function
     *
     * @return self<V,T>
     */
    public function keyBy(callable $function): self;

    /**
     * @return self<V,K>
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

    /**
     * @template D
     *
     * @param mixed|callable(V,K):bool $specification
     * @param D                        $default
     *
     * @return V|D
     *
     * @throws InvalidSpecification if $specification is not a valid specification
     */
    public function find(mixed $specification, mixed $default = null): mixed;

    /**
     * @template T
     *
     * @param callable(T,V,K):T $function
     * @param T                 $initial
     *
     * @return T
     */
    public function reduce(callable $function, mixed $initial = null): mixed;

    public function isEmpty(): bool;

    /**
     * @return ArrayCollection<V,K&array-key>
     */
    public function eager(): ArrayCollection;

    /**
     * @param positive-int $page
     * @param positive-int $limit
     *
     * @return Page<V,K>
     */
    public function paginate(int $page = 1, int $limit = Page::DEFAULT_LIMIT): Page;

    /**
     * @param positive-int $limit
     *
     * @return Pages<V,K>
     */
    public function pages(int $limit = Page::DEFAULT_LIMIT): Pages;
}
