<?php

use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\LazyCollection;
use function PHPStan\Testing\assertType;
use function Zenstruck\collect;

assertType('Zenstruck\Collection\LazyCollection<int, User>', new LazyCollection([new User]));

/** @var ObjectRepository<User> $objectRepository */

assertType('Traversable<int, User>', $objectRepository->getIterator());
assertType('User|null', $objectRepository->find(1));
assertType('Zenstruck\Collection\Doctrine\Result<User>', $objectRepository->filter([]));
assertType('array<int, User>', $objectRepository->filter([])->eager()->all());
assertType('User|null', $objectRepository->filter([])->first());
assertType('Zenstruck\Collection\Doctrine\Result<bool|float|int|string>', $objectRepository->filter([])->asScalar());
assertType('Zenstruck\Collection\Doctrine\Result<string>', $objectRepository->filter([])->asString());
assertType('Zenstruck\Collection\Doctrine\Result<int>', $objectRepository->filter([])->asInt());
assertType('Zenstruck\Collection\Doctrine\Result<float>', $objectRepository->filter([])->asFloat());
assertType('Zenstruck\Collection\Doctrine\Result<array<string, mixed>>', $objectRepository->filter([])->asArray());
assertType('Zenstruck\Collection\Doctrine\Result<int>', $objectRepository->filter([])->as(fn(): int => 1));
assertType('Zenstruck\Collection\Page<int, User>', $objectRepository->filter([])->paginate());

/** @var EntityRepository<User> $ormRepository */

assertType('Zenstruck\Collection\Doctrine\ORM\EntityResult<int>', $ormRepository->filter([])->as(fn(): int => 1));
assertType('Zenstruck\Collection\Doctrine\ORM\EntityResult<Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates<User>>', $ormRepository->filter([])->withAggregates());

class User
{
}

assertType('Zenstruck\Collection\LazyCollection<*NEVER*, *NEVER*>', collect());
assertType('Zenstruck\Collection\LazyCollection<*NEVER*, *NEVER*>', collect(null));

/**
 * @param User[]|null $users
 * @return LazyCollection<int, User>
 */
function get_users(array|null $users): LazyCollection
{
    return collect($users);
}

assertType('Zenstruck\Collection\LazyCollection<int, User>', get_users(null));
assertType('Zenstruck\Collection\LazyCollection<int, User>', get_users([]));
assertType('Zenstruck\Collection\LazyCollection<int, User>', get_users([new User()]));
