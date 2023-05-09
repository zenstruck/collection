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

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\EntityResult;
use Zenstruck\Collection\Tests\CollectionTests;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class EntityResultTest extends TestCase
{
    use CollectionTests, HasDatabase;

    /**
     * @test
     */
    public function ensure_type_matches(): void
    {
        $results = $this->createWithItems(1);
        $expected = $this->expectedValueAt(1);

        if (\is_object($expected)) {
            $this->assertEquals($this->expectedValueAt(1), $results->first());
            $this->assertEquals([$expected], \iterator_to_array($results));
            $this->assertEquals([$expected], $results->eager()->all());

            return;
        }

        $this->assertSame($this->expectedValueAt(1), $results->first());
        $this->assertSame([$expected], \iterator_to_array($results));
        $this->assertSame([$expected], $results->eager()->all());
    }

    abstract protected function createWithItems(int $count): EntityResult;
}
