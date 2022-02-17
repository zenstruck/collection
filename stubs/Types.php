<?php

use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\DoctrineCollection;
use function PHPStan\Testing\assertType;

assertType('Zenstruck\Collection\IterableCollection<int, User>', new IterableCollection([new User]));

/** @var DoctrineCollection<int,User> $doctrineCollection */

assertType('Traversable<int, User>', $doctrineCollection->getIterator());
assertType('Zenstruck\Collection\Page<User>', $doctrineCollection->paginate());
assertType('User|null', $doctrineCollection->get(1));
assertType('Zenstruck\Collection\DoctrineCollection<int, User>', $doctrineCollection->take(1));
assertType('Zenstruck\Collection\DoctrineCollection<int, User>', $doctrineCollection->filter(fn(User $user) => true));

/** @var ORMRepository<User> $ormRepository */

assertType('Traversable<int, User>', $ormRepository->getIterator());
assertType('Zenstruck\Collection<int, User>', $ormRepository->take(1, 2));
assertType('Traversable<int, User>', $ormRepository->batch());
assertType('Traversable<int, User>', $ormRepository->batchProcess());

assertType('User|null', $ormRepository->find(1));
assertType('array<User>', $ormRepository->findAll());
assertType('array<User>', $ormRepository->findBy([]));
assertType('User|null', $ormRepository->findOneBy([]));

assertType('Zenstruck\Collection\Page<User>', $ormRepository->paginate());
assertType('Traversable<int, User>', $ormRepository->paginate()->getIterator());
assertType('Zenstruck\Collection\PageCollection<User>', $ormRepository->pages());
assertType('Zenstruck\Collection\Page<User>', $ormRepository->pages()->get(1));
assertType('Traversable<int, Zenstruck\Collection\Page<User>>', $ormRepository->pages()->getIterator());

assertType('ORMResult<User>', $ormRepository->match('spec'));
assertType('Traversable<int, User>', $ormRepository->match('spec')->getIterator());
assertType('Zenstruck\Collection\Page<User>', $ormRepository->match('spec')->paginate());
assertType('User', $ormRepository->matchOne('spec'));

assertType('ORMRepository<User>', $ormRepository->flush());
assertType('ORMRepository<User>', $ormRepository->remove(new User));
assertType('ORMRepository<User>', $ormRepository->add(new User));

/** @var DBALRepository<User> $dbalRepository */

assertType('Traversable<int, User>', $dbalRepository->getIterator());
assertType('Zenstruck\Collection<int, User>', $dbalRepository->take(1, 2));

assertType('Zenstruck\Collection\Doctrine\DBAL\Result<User>', $dbalRepository->match('spec'));
assertType('Traversable<int, User>', $dbalRepository->match('spec')->getIterator());
assertType('Zenstruck\Collection\Page<User>', $dbalRepository->match('spec')->paginate());
assertType('User', $dbalRepository->matchOne('spec'));

assertType('Zenstruck\Collection\Page<User>', $dbalRepository->paginate());
assertType('Traversable<int, User>', $dbalRepository->paginate()->getIterator());
assertType('Zenstruck\Collection\PageCollection<User>', $dbalRepository->pages());
assertType('Zenstruck\Collection\Page<User>', $dbalRepository->pages()->get(1));
assertType('Traversable<int, Zenstruck\Collection\Page<User>>', $dbalRepository->pages()->getIterator());

class User
{
}
