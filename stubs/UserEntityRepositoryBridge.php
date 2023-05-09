<?php

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\EntityRepositoryBridge;

/**
 * @extends ServiceEntityRepository<User>
 * @implements ObjectRepository<User>
 */
final class UserEntityRepositoryBridge extends ServiceEntityRepository implements ObjectRepository
{
    /** @use EntityRepositoryBridge<User> */
    use EntityRepositoryBridge;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
}
