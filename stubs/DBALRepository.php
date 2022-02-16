<?php

use Doctrine\DBAL\Connection;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\ObjectRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository\CollectionRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository\MatchableRepository;
use Zenstruck\Collection\Matchable;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value
 * @extends ObjectRepository<Value>
 * @implements Collection<int,Value>
 * @implements Matchable<int,Value>
 */
final class DBALRepository extends ObjectRepository implements Collection, Matchable
{
    /** @use CollectionRepository<Value> */
    use CollectionRepository;

    /** @use MatchableRepository<Value> */
    use MatchableRepository;

    /** @use Paginatable<Value> */
    use Paginatable;

    protected static function createObject(array $data): object
    {
        return new \stdClass();
    }

    protected static function tableName(): string
    {
        return 'table';
    }

    protected function connection(): Connection
    {
        return 'connection'; // @phpstan-ignore-line
    }
}
