<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Symfony\Doctrine;

use Symfony\Contracts\Service\ResetInterface;
use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Doctrine\ObjectRepositoryFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ChainObjectRepositoryFactory implements ObjectRepositoryFactory, ResetInterface
{
    /** @var array<class-string,ObjectRepository<object>> */
    private array $cache = [];

    public function __construct(private ObjectRepositoryFactory $inner)
    {
    }

    public function create(string $class): ObjectRepository
    {
        return $this->cache[$class] ??= $this->inner->create($class); // @phpstan-ignore-line
    }

    public function reset(): void
    {
        $this->cache = [];
    }
}
