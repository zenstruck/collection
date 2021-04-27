<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Page;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PageTest extends TestCase
{
    use CountableIteratorTests;

    /**
     * @test
     */
    public function it_properly_handles_page1(): void
    {
        $pager = $this->createPage(\range(1, 504), 1, 20);

        $this->assertTrue($pager->haveToPaginate());
        $this->assertSame(1, $pager->currentPage());
        $this->assertSame(1, $pager->firstPage());
        $this->assertSame(2, $pager->nextPage());
        $this->assertNull($pager->previousPage());
        $this->assertSame(26, $pager->lastPage());
        $this->assertSame(26, $pager->pageCount());
        $this->assertSame(504, $pager->totalCount());
        $this->assertSame(20, $pager->limit());
        $this->assertCount(20, $pager);
        $this->assertSame(\range(1, 20), \iterator_to_array($pager));
    }

    /**
     * @test
     */
    public function it_properly_handles_page2(): void
    {
        $pager = $this->createPage(\range(1, 504), 2, 20);

        $this->assertTrue($pager->haveToPaginate());
        $this->assertSame(2, $pager->currentPage());
        $this->assertSame(1, $pager->firstPage());
        $this->assertSame(3, $pager->nextPage());
        $this->assertSame(1, $pager->previousPage());
        $this->assertCount(20, $pager);
        $this->assertSame(\range(21, 40), \array_values(\iterator_to_array($pager)));
    }

    /**
     * @test
     */
    public function it_properly_handles_the_last_page(): void
    {
        $pager = $this->createPage(\range(1, 504), 26, 20);

        $this->assertTrue($pager->haveToPaginate());
        $this->assertSame(26, $pager->currentPage());
        $this->assertSame(1, $pager->firstPage());
        $this->assertNull($pager->nextPage());
        $this->assertSame(25, $pager->previousPage());
        $this->assertCount(4, $pager);
        $this->assertSame(\range(501, 504), \array_values(\iterator_to_array($pager)));
    }

    /**
     * @test
     */
    public function it_properly_handles_an_empty_page(): void
    {
        $pager = $this->createPage([], 1, 20);

        $this->assertFalse($pager->haveToPaginate());
        $this->assertSame(1, $pager->currentPage());
        $this->assertSame(1, $pager->firstPage());
        $this->assertNull($pager->nextPage());
        $this->assertNull($pager->previousPage());
        $this->assertSame(1, $pager->lastPage());
        $this->assertSame(1, $pager->pageCount());
        $this->assertSame(0, $pager->totalCount());
        $this->assertCount(0, $pager);
        $this->assertSame([], \iterator_to_array($pager));
    }

    /**
     * @test
     */
    public function it_properly_handles_a_single_page(): void
    {
        $pager = $this->createPage(\range(1, 10), 1, 20);

        $this->assertFalse($pager->haveToPaginate());
        $this->assertSame(1, $pager->currentPage());
        $this->assertSame(1, $pager->firstPage());
        $this->assertNull($pager->nextPage());
        $this->assertNull($pager->previousPage());
        $this->assertSame(1, $pager->lastPage());
        $this->assertSame(1, $pager->pageCount());
        $this->assertSame(10, $pager->totalCount());
        $this->assertCount(10, $pager);
        $this->assertSame(\range(1, 10), \iterator_to_array($pager));
    }

    /**
     * @test
     */
    public function it_properly_handles_a_too_large_page_as_the_last_page(): void
    {
        $pager = $this->createPage(\range(1, 504), 30, 20);

        $this->assertSame(26, $pager->currentPage());
        $this->assertSame(1, $pager->firstPage());
        $this->assertNull($pager->nextPage());
        $this->assertSame(25, $pager->previousPage());
        $this->assertCount(4, $pager);
        $this->assertSame(\range(501, 504), \array_values(\iterator_to_array($pager)));
    }

    /**
     * @test
     */
    public function invalid_page(): void
    {
        $pager = $this->createPage([], 0);
        $this->assertSame(1, $pager->currentPage());

        $pager = $this->createPage([], -1);
        $this->assertSame(1, $pager->currentPage());
    }

    /**
     * @test
     */
    public function invalid_limit(): void
    {
        $pager = $this->createPage([], 1, 0);
        $this->assertSame(20, $pager->limit());

        $pager = $this->createPage([], 1, -1);
        $this->assertSame(20, $pager->limit());
    }

    protected function createWithItems(int $count): Page
    {
        return $this->createPage($count ? \range(1, $count) : [], 1, (int) \INF);
    }

    private function createPage(array $results, int $page, int $limit = Page::DEFAULT_LIMIT): Page
    {
        return new Page(new IterableCollection($results), $page, $limit);
    }
}
