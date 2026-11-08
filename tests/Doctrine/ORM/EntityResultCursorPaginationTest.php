<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine\ORM;

use Doctrine\ORM\Tools\Pagination\CursorPaginator;
use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Spec;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class EntityResultCursorPaginationTest extends TestCase
{
    use HasDatabase;

    /**
     * @test
     * @dataProvider fetchJoins
     */
    public function navigates_in_both_directions_with_a_tie_breaker(bool $fetchJoins): void
    {
        $this->requireCursorPaginator();

        foreach (['b', 'b', 'b', 'a', 'a'] as $value) {
            $this->em->persist(new Entity($value));
        }

        $this->flushAndClear();

        $repository = new EntityRepository($this->em, Entity::class);
        $result = $repository->filter(Spec::andX(Spec::sortDesc('value'), Spec::sortDesc('id')));
        $result = $result->as(static fn(Entity $entity) => $entity->id);

        if (!$fetchJoins) {
            $result = $result->disableFetchJoins();
        }

        $this->assertQueryCount($fetchJoins ? 2 : 1, function() use ($result) {
            $first = $result->cursorPaginate(limit: 2);
            $this->assertSame([3, 2], \iterator_to_array($first));
            $this->assertCount(2, $first);
            $this->assertTrue($first->hasNext());
            $this->assertFalse($first->hasPrevious());
            $this->assertNull($first->previousCursor());
            $this->assertNotNull($first->nextCursor());
        });

        $first = $result->cursorPaginate(limit: 2);
        $middle = $result->cursorPaginate($first->nextCursor(), limit: 2);
        $this->assertSame([1, 5], \iterator_to_array($middle));
        $this->assertNotNull($middle->previousCursor());
        $this->assertNotNull($middle->nextCursor());

        $last = $result->cursorPaginate($middle->nextCursor(), limit: 2);
        $this->assertSame([4], \iterator_to_array($last));
        $this->assertFalse($last->hasNext());
        $this->assertTrue($last->hasPrevious());
        $this->assertNull($last->nextCursor());
        $this->assertNotNull($last->previousCursor());

        $back = $result->cursorPaginate($last->previousCursor(), limit: 2);
        $this->assertSame([1, 5], \iterator_to_array($back));
        $this->assertNotNull($back->previousCursor());
        $this->assertNotNull($back->nextCursor());

        $back = $result->cursorPaginate($back->previousCursor(), limit: 2);
        $this->assertSame([3, 2], \iterator_to_array($back));
        $this->assertNull($back->previousCursor());
        $this->assertNotNull($back->nextCursor());
    }

    /**
     * @test
     */
    public function returns_no_cursors_when_a_page_becomes_empty(): void
    {
        $this->requireCursorPaginator();
        $this->persistEntities(2);

        $repository = new EntityRepository($this->em, Entity::class);
        $result = $repository->filter(Spec::sortAsc('id'));
        $first = $result->cursorPaginate(limit: 1);

        $delete = $this->em->createQuery('DELETE FROM '.Entity::class.' e WHERE e.id = 2');
        $delete->execute();

        $empty = $result->cursorPaginate($first->nextCursor(), limit: 1);
        $this->assertCount(0, $empty);
        $this->assertNull($empty->nextCursor());
        $this->assertNull($empty->previousCursor());
    }

    /**
     * @test
     */
    public function rejects_array_hydration(): void
    {
        $this->requireCursorPaginator();

        $repository = new EntityRepository($this->em, Entity::class);
        $result = $repository->filter(Spec::sortAsc('id'));
        $result = $result->asArray();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cursor pagination requires a query that returns root entities.');

        $result->cursorPaginate();
    }

    /**
     * @test
     */
    public function requires_doctrine_orm_3_7(): void
    {
        if (\class_exists(CursorPaginator::class)) {
            self::markTestSkipped('Doctrine ORM supports cursor pagination.');
        }

        $repository = new EntityRepository($this->em, Entity::class);
        $result = $repository->filter(Spec::sortAsc('id'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cursor pagination requires doctrine/orm 3.7 or later.');

        $result->cursorPaginate();
    }

    public static function fetchJoins(): iterable
    {
        yield 'fetch joins enabled' => [true];
        yield 'fetch joins disabled' => [false];
    }

    private function requireCursorPaginator(): void
    {
        if (!\class_exists(CursorPaginator::class)) {
            self::markTestSkipped('Doctrine ORM 3.7 or later is required.');
        }
    }
}
