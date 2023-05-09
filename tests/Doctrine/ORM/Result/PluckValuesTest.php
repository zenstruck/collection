<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Result;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\EntityResult;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PluckValuesTest extends TestCase
{
    use HasDatabase;

    /**
     * @test
     */
    public function can_pluck_int(): void
    {
        $this->assertSame(1, $this->createWithItems(1)->asInt('id')->first());
    }

    /**
     * @test
     */
    public function can_pluck_scalar(): void
    {
        $this->assertEquals('1', $this->createWithItems(1)->asScalar('id')->first());
    }

    /**
     * @test
     */
    public function can_pluck_float(): void
    {
        $this->assertSame(1.0, $this->createWithItems(1)->asFloat('id')->first());
    }

    /**
     * @test
     */
    public function can_pluck_string(): void
    {
        $this->assertSame('1', $this->createWithItems(1)->asString('id')->first());
    }

    /**
     * @test
     */
    public function can_pluck_array_values(): void
    {
        $this->assertSame(['id' => 1, 'value' => 'value 1'], $this->createWithItems(1)->asArray('id', 'value')->first());
    }

    /**
     * @test
     */
    public function can_use_a_custom_modifier(): void
    {
        $result = $this->createWithItems(1)->asArray('id', 'value')->as(fn(array $row) => (object) $row)->first();

        $this->assertEquals((object) ['id' => 1, 'value' => 'value 1'], $result);
    }

    protected function createWithItems(int $count): EntityResult
    {
        $this->persistEntities($count);

        return new EntityResult($this->em->createQueryBuilder()->select('e')->from(Entity::class, 'e'));
    }
}
