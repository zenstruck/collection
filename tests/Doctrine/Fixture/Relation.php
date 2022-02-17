<?php

namespace Zenstruck\Collection\Tests\Doctrine\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ORM\Entity, ORM\Table(name: 'relations')]
class Relation
{
    public const TABLE = 'relations';

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue]
    public ?int $id;

    #[ORM\Column(type: 'string')]
    public int $value;

    public function __construct(int $value, ?int $id = null)
    {
        $this->id = $id;
        $this->value = $value;
    }
}
