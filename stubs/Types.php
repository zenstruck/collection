<?php

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

assertType('Zenstruck\Collection\Doctrine\ORM\Result<User>', $ormRepository->filter('spec'));
assertType('array<int, User>', $ormRepository->filter('spec')->toArray());
assertType('User|null', $ormRepository->filter('spec')->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<bool|float|int|string>', $ormRepository->filter('spec')->asScalar());
assertType('array<int, bool|float|int|string>', $ormRepository->filter('spec')->asScalar()->toArray());
assertType('bool|float|int|string|null', $ormRepository->filter('spec')->asScalar()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<float>', $ormRepository->filter('spec')->asFloat());
assertType('array<int, float>', $ormRepository->filter('spec')->asFloat()->toArray());
assertType('float|null', $ormRepository->filter('spec')->asFloat()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<int>', $ormRepository->filter('spec')->asInt());
assertType('array<int, int>', $ormRepository->filter('spec')->asInt()->toArray());
assertType('int|null', $ormRepository->filter('spec')->asInt()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<string>', $ormRepository->filter('spec')->asString());
assertType('array<int, string>', $ormRepository->filter('spec')->asString()->toArray());
assertType('string|null', $ormRepository->filter('spec')->asString()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<array>', $ormRepository->filter('spec')->asArray());
assertType('array<int, array>', $ormRepository->filter('spec')->asArray()->toArray());
assertType('array|null', $ormRepository->filter('spec')->asArray()->first());
assertType('Zenstruck\Collection\Doctrine\ORM\Result<Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates<User>>', $ormRepository->filter('spec')->withAggregates());
assertType('array<int, Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates<User>>', $ormRepository->filter('spec')->withAggregates()->toArray());
assertType('Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates<User>|null', $ormRepository->filter('spec')->withAggregates()->first());
assertType('Traversable<int, User>', $ormRepository->filter('spec')->getIterator());
assertType('Zenstruck\Collection\Page<User>', $ormRepository->filter('spec')->paginate());
assertType('User', $ormRepository->get('spec'));

assertType('ORMRepository<User>', $ormRepository->flush());
assertType('ORMRepository<User>', $ormRepository->remove(new User));
assertType('ORMRepository<User>', $ormRepository->add(new User));
assertType('ORMRepository<User>', $ormRepository->save(new User));

/** @var DBALRepository<User> $dbalRepository */

assertType('Traversable<int, User>', $dbalRepository->getIterator());

assertType('Zenstruck\Collection\Doctrine\DBAL\Result<User>', $dbalRepository->filter('spec'));
assertType('Traversable<int, User>', $dbalRepository->filter('spec')->getIterator());
assertType('Zenstruck\Collection\Page<User>', $dbalRepository->filter('spec')->paginate());
assertType('User', $dbalRepository->get('spec'));

class User
{
}
