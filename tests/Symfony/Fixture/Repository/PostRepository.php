<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Symfony\Fixture\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\EntityRepositoryBridge;
use Zenstruck\Collection\Tests\Symfony\Fixture\Entity\Post;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends ServiceEntityRepository<Post>
 * @implements ObjectRepository<\Zenstruck\Collection\Tests\Symfony\Fixture\Entity\Post>
 */
final class PostRepository extends ServiceEntityRepository implements ObjectRepository
{
    /** @use EntityRepositoryBridge<Post> */
    use EntityRepositoryBridge;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }
}
