<?php

namespace Zenstruck\Collection\Tests;

use Zenstruck\Collection\IterableCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayCollectionTest extends IterableCollectionTest
{
    /**
     * @test
     */
    public function filter(): void
    {
        parent::filter();

        // with no callable
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function reject(): void
    {
        parent::reject();

        // with no callable
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function key_by(): void
    {
        parent::key_by();

        // with \Stringable object as key
        $this->markTestIncomplete();
    }

    public function map_with_keys(): void
    {
        parent::map_with_keys();

        // with \Stringable object as key
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function all(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function keys(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function values(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function reverse(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function slice(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function merge(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function sort(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function sort_desc(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function sort_by(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function sort_by_desc(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function sort_keys(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function sort_keys_desc(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function combine(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function combine_with_self(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function group_by(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function get(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function set(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function forget(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function only(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function push(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function in(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function key_exists(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function implode(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function array_accessor(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function explode_constructor(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function range_constructor(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function fill_constructor(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function fill_keys_constructor(): void
    {
        $this->markTestIncomplete();
    }

    protected function createWithItems(int $count): IterableCollection
    {
        // todo use array collection
        return new IterableCollection($count ? \range(1, $count) : []);
    }
}
