<?php

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\CollectionRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\EntityRepositoryMixin;
use Zenstruck\Collection\Doctrine\ORM\Repository\Flushable;
use Zenstruck\Collection\Doctrine\ORM\Repository\MatchableRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\Removable;
use Zenstruck\Collection\Doctrine\ORM\Repository\Writable;
use Zenstruck\Collection\Matchable;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value of object
 * @extends ObjectRepository<Value>
 * @implements Collection<int,Value>
 * @implements Matchable<int,Value>
 */
abstract class ORMRepository extends ObjectRepository implements Collection, Matchable
{
    /** @use CollectionRepository<Value> */
    use CollectionRepository;
    use EntityRepositoryMixin;
    use Flushable;

    /** @use MatchableRepository<Value> */
    use MatchableRepository;

    /** @use Paginatable<Value> */
    use Paginatable;

    /** @use Removable<Value> */
    use Removable;

    /** @use Writable<Value> */
    use Writable;
}
