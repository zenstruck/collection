<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use Zenstruck\Collection\Doctrine\ORM\ObjectRepository;

/**
 * Allows your repository to have access to {@see \Doctrine\ORM\EntityRepository}
 * methods (except "count()").
 *
 * @method QueryBuilder            createQueryBuilder(string $alias, ?string $indexBy = null)
 * @method ResultSetMappingBuilder createResultSetMappingBuilder(string $alias)
 * @method Query                   createNamedQuery(string $queryName)
 * @method NativeQuery             createNativeNamedQuery(string $queryName)
 * @method void                    clear()
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait AsEntityRepository
{
    /**
     * @param mixed[] $arguments
     */
    final public function __call(string $name, array $arguments): mixed
    {
        if (!$this instanceof ObjectRepository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, ObjectRepository::class));
        }

        return $this->repo()->{$name}(...$arguments);
    }
}
