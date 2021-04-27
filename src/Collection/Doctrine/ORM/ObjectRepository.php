<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository as DoctrineObjectRepository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class ObjectRepository extends Repository implements DoctrineObjectRepository
{
    /**
     * @see EntityRepository::find()
     */
    final public function find($id, ?int $lockMode = null, ?int $lockVersion = null): ?object
    {
        return $this->repo()->find($id, $lockMode, $lockVersion);
    }

    /**
     * @see EntityRepository::findAll()
     */
    final public function findAll(): array
    {
        return $this->repo()->findAll();
    }

    /**
     * @see EntityRepository::findBy()
     */
    final public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->repo()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @see EntityRepository::findOneBy()
     */
    final public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        return $this->repo()->findOneBy($criteria, $orderBy);
    }
}
