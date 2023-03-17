<?php

use Zenstruck\Collection\Doctrine\DBAL\Repository;
use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\DoctrineCollection;
use function PHPStan\Testing\assertType;

assertType('Zenstruck\Collection\LazyCollection<int, User>', new LazyCollection([new User]));

/** @var DoctrineCollection<int,User> $doctrineCollection */

assertType('Traversable<int, User>', $doctrineCollection->getIterator());
assertType('Zenstruck\Collection\Page<User>', $doctrineCollection->paginate());
assertType('User|null', $doctrineCollection->get(1));
assertType('Zenstruck\Collection\DoctrineCollection<int, User>', $doctrineCollection->take(1));
assertType('Zenstruck\Collection\DoctrineCollection<int, User>', $doctrineCollection->filter(fn(User $user) => true));

/** @var ORMRepository<User> $ormRepository */

assertType('Traversable<int, User>', $ormRepository->getIterator());
assertType('Traversable<int, User>', $ormRepository->batch());
assertType('Traversable<int, User>', $ormRepository->batchProcess());

assertType('User|null', $ormRepository->find(1));
assertType('array<int, User>', $ormRepository->findAll());
assertType('array<int, User>', $ormRepository->findBy([]));
assertType('User|null', $ormRepository->findOneBy([]));

assertType('Zenstruck\Collection\Doctrine\ORM\Result<User>', $ormRepository->filter([]));
assertType('array<int, User>', $ormRepository->filter([])->toArray());
assertType('User|null', $ormRepository->filter([])->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<bool|float|int|string>', $ormRepository->filter([])->asScalar());
assertType('array<int, bool|float|int|string>', $ormRepository->filter([])->asScalar()->toArray());
assertType('bool|float|int|string|null', $ormRepository->filter([])->asScalar()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<float>', $ormRepository->filter([])->asFloat());
assertType('array<int, float>', $ormRepository->filter([])->asFloat()->toArray());
assertType('float|null', $ormRepository->filter([])->asFloat()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<int>', $ormRepository->filter([])->asInt());
assertType('array<int, int>', $ormRepository->filter([])->asInt()->toArray());
assertType('int|null', $ormRepository->filter([])->asInt()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<string>', $ormRepository->filter([])->asString());
assertType('array<int, string>', $ormRepository->filter([])->asString()->toArray());
assertType('string|null', $ormRepository->filter([])->asString()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<array>', $ormRepository->filter([])->asArray());
assertType('array<int, array>', $ormRepository->filter([])->asArray()->toArray());
assertType('array|null', $ormRepository->filter([])->asArray()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates<User>>', $ormRepository->filter([])->withAggregates());
assertType('array<int, Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates<User>>', $ormRepository->filter([])->withAggregates()->toArray());
assertType('Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates<User>|null', $ormRepository->filter([])->withAggregates()->first());
assertType('Traversable<int, User>', $ormRepository->filter([])->getIterator());
assertType('Zenstruck\Collection\Page<User>', $ormRepository->filter([])->paginate());
assertType('User', $ormRepository->get([]));

assertType('ORMRepository<User>', $ormRepository->flush());
assertType('ORMRepository<User>', $ormRepository->remove(new User));
assertType('ORMRepository<User>', $ormRepository->add(new User));
assertType('ORMRepository<User>', $ormRepository->save(new User));

/** @var Repository<User> $dbalRepository */

assertType('Traversable<int, User>', $dbalRepository->getIterator());

class User
{
}
