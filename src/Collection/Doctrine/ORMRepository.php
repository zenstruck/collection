<?php

namespace Zenstruck\Collection\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\QueryBuilder;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\AsEntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\AsService;
use Zenstruck\Collection\Doctrine\ORM\Repository\Flushable;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsMatchable;
use Zenstruck\Collection\Doctrine\ORM\Repository\Removable;
use Zenstruck\Collection\Doctrine\ORM\Repository\Writable;
use Zenstruck\Collection\Paginatable;

/**
 * General purpose ORM repository base class.
 *
 * - Countable: `count($repo)`
 * - Lazy-iterable: `foreach ($repo as $object)`
 * - Instance of {@see \Doctrine\Persistence\ObjectRepository}
 * - Instance of {@see Collection}
 * - Can paginate: `$repo->paginate($page)`
 * - Can "flush": `$repo->flush()`
 * - Can add objects: `$repo->add($object)`
 * - Can remove objects: `$repo->remove($object)`
 *
 * @see AsEntityRepository to add traditional `EntityManager` methods (ie `$repo->findOneBy*()/$repo->findBy*()/$repo->createQueryBuilder()`)
 * @see AsService and implement {@see ServiceEntityRepositoryInterface} to autowire with Symfony (removes need to implement {@see ObjectRepository::em()})
 * @see IsMatchable to use the specification system
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V of object
 * @extends ObjectRepository<V>
 * @implements Collection<int,V>
 */
abstract class ORMRepository extends ObjectRepository implements Collection
{
    /**
     * @use IsCollection<V>
     * @use Paginatable<V>
     * @use Removable<V>
     * @use Writable<V>
     */
    use Flushable, IsCollection, Paginatable, Removable, Writable;

    /**
     * @return ORMResult<V>
     */
    protected static function createResult(QueryBuilder $qb, bool $fetchCollection = true, ?bool $useOutputWalkers = null): ORMResult
    {
        return new ORMResult($qb, $fetchCollection, $useOutputWalkers);
    }
}
