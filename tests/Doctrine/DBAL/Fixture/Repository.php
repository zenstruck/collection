<?php

namespace Zenstruck\Collection\Tests\Doctrine\DBAL\Fixture;

use Doctrine\DBAL\Connection;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\Repository as BaseRepository;
use Zenstruck\Collection\Paginatable;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Repository extends BaseRepository implements Collection
{
    use Paginatable;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function take(int $limit, int $offset = 0): Collection
    {
        return static::createResult($this->qb())->take($limit, $offset);
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
