<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\AsEntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\Flushable;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsMatchable;
use Zenstruck\Collection\Doctrine\ORM\Repository\Removable;
use Zenstruck\Collection\Doctrine\ORM\Repository\Writable;
use Zenstruck\Collection\Matchable;
use Zenstruck\Collection\Paginatable;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class KitchenSinkRepository extends ObjectRepository implements Collection, Matchable
{
    use AsEntityRepository, Flushable, IsCollection, IsMatchable, Paginatable, Removable, Writable;

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getClassName(): string
    {
        return Entity::class;
    }

    protected function em(): EntityManagerInterface
    {
        return $this->em;
    }
}
