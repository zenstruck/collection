<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: 'shapes')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['circle' => Circle::class, 'square' => Square::class])]
abstract class Shape
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    public function __construct(#[ORM\Column] public string $name)
    {
    }
}
