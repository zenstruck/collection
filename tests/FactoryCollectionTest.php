<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection;
use Zenstruck\Collection\FactoryCollection;
use Zenstruck\Collection\LazyCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FactoryCollectionTest extends TestCase
{
    use CollectionTests;

    /**
     * @test
     */
    public function is_empty_reads_only_the_first_item_without_calling_the_factory(): void
    {
        $source = new LazyCollection(static function() {
            yield null;

            throw new \LogicException('The source should not advance past the first item.');
        });

        $collection = new FactoryCollection($source, static fn() => throw new \LogicException('The factory should not be called.'));

        $this->assertFalse($collection->isEmpty());
    }

    /**
     * @test
     */
    public function factory_receives_only_the_value_by_default(): void
    {
        $source = new LazyCollection(['key' => 'value']);
        $collection = new FactoryCollection($source, \strlen(...));

        $this->assertSame(['key' => 5], \iterator_to_array($collection));
    }

    /**
     * @test
     */
    public function take_maps_only_the_slice_with_its_original_keys(): void
    {
        $source = new LazyCollection([10 => 'skip', 'keep' => 'a', 20 => 'b', 30 => 'skip']);
        $mappedKeys = [];
        $mapped = $source->map(static function(string $value, int|string $key) use (&$mappedKeys): string {
            $mappedKeys[] = $key;

            return "{$key}:{$value}";
        });
        $slice = $mapped->take(2, 1);

        $this->assertSame([], $mappedKeys);
        $this->assertSame(['keep' => 'keep:a', 20 => '20:b'], \iterator_to_array($slice));
        $this->assertSame(['keep', 20], $mappedKeys);
    }

    protected function createWithItems(int $count): Collection
    {
        return new FactoryCollection(new LazyCollection($count ? \range(1, $count) : []), static fn($position) => "value {$position}");
    }

    protected function expectedValueAt(int $position): string
    {
        return "value {$position}";
    }
}
