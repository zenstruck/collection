<?php

namespace Zenstruck\Collection\Tests\Bridge;

use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Bridge\PagerfantaAdapter;
use Zenstruck\Collection\IterableCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PagerfantaAdapterTest extends TestCase
{
    /**
     * @test
     */
    public function it_counts_total_number_of_results(): void
    {
        $pagerfanta = new Pagerfanta(new PagerfantaAdapter(new IterableCollection([1, 2, 3, 4])));

        $this->assertEquals(4, $pagerfanta->getNbResults());
    }

    /**
     * @test
     */
    public function it_iterates_slice(): void
    {
        $pagerfanta = new Pagerfanta(new PagerfantaAdapter(new IterableCollection([1, 2, 3, 4])));

        $pagerfanta->setMaxPerPage(2);
        $pagerfanta->setCurrentPage(1);

        $this->assertEquals([1, 2], \iterator_to_array($pagerfanta->getCurrentPageResults()));

        $pagerfanta->setCurrentPage(2);

        $this->assertEquals([2 => 3, 3 => 4], \iterator_to_array($pagerfanta->getCurrentPageResults()));
    }
}
