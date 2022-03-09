<?php

namespace Zenstruck\Collection\Tests\Doctrine\DBAL\Fixture;

use Doctrine\DBAL\Connection;
use Zenstruck\Collection\Doctrine\DBAL\Repository as BaseRepository;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Repository extends BaseRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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
