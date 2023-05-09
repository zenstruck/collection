<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Symfony\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zenstruck\Collection\Tests\Symfony\Fixture\Repository\PostRepository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\Column]
    public int $id;
}
