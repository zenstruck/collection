<?php

namespace Zenstruck\Collection\Tests\Doctrine\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ORM\Entity, ORM\Table(name: 'entities')]
class Entity
{
    public const TABLE = 'entities';

    #[ORM\Id, ORM\Column(type: 'integer'), ORM\GeneratedValue]
    public ?int $id;

    #[ORM\Column(type: 'string')]
    public string $value;

    #[ORM\ManyToOne(targetEntity: Relation::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'relation_id', referencedColumnName: 'id', nullable: true)]
    public ?Relation $relation = null;

    public function __construct(string $value, ?int $id = null)
    {
        $this->id = $id;
        $this->value = $value;
    }

    public static function withRelation(string $value, Relation $relation): self
    {
        $entity = new self($value);
        $entity->relation = $relation;

        return $entity;
    }
}
