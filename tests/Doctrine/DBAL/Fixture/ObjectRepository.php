<?php

namespace Zenstruck\Collection\Tests\Doctrine\DBAL\Fixture;

use Doctrine\DBAL\Connection;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\ObjectRepository as BaseObjectRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\DBAL\Repository\IsMatchable;
use Zenstruck\Collection\Matchable;
use Zenstruck\Collection\Paginatable;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ObjectRepository extends BaseObjectRepository implements Collection, Matchable
{
    use IsCollection, IsMatchable, Paginatable;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    protected static function createObject(array $data): Entity
    {
        return new Entity($data['value'], $data['id']);
    }

    protected static function tableName(): string
    {
        return Entity::TABLE;
    }

    protected function connection(): Connection
    {
        return $this->connection;
    }
}
