<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Symfony;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Collection\Page;

use function Zenstruck\collect;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PagerTemplateTest extends KernelTestCase
{
    /**
     * @test
     */
    public function simple_pager(): void
    {
        $html = $this->render('_simple', new Page(collect(\range(1, 50)), 3, 10));

        $this->assertStringContainsString('<a href="/posts?page=2" rel="prev">Previous</a>', $html);
        $this->assertStringContainsString('<a href="/posts?page=4" rel="next">Next</a>', $html);
    }

    /**
     * @test
     */
    public function simple_pager_disables_the_boundaries(): void
    {
        $first = $this->render('_simple', new Page(collect(\range(1, 50)), 1, 10));

        $this->assertStringContainsString('<li class="disabled"><span>Previous</span></li>', $first);
        $this->assertStringContainsString('rel="next"', $first);

        $last = $this->render('_simple', new Page(collect(\range(1, 50)), 5, 10));

        $this->assertStringContainsString('rel="prev"', $last);
        $this->assertStringContainsString('<li class="disabled"><span>Next</span></li>', $last);
    }

    /**
     * @test
     */
    public function pagers_render_nothing_for_a_single_page(): void
    {
        $this->assertSame('', \trim($this->render('_simple', new Page(collect(\range(1, 5)), 1, 10))));
        $this->assertSame('', \trim($this->render('_full', new Page(collect(\range(1, 5)), 1, 10))));
    }

    /**
     * @test
     */
    public function full_pager(): void
    {
        $html = $this->render('_full', new Page(collect(\range(1, 50)), 3, 10));

        foreach (\range(1, 5) as $page) {
            if (3 === $page) {
                $this->assertStringContainsString('<li class="active"><span>3</span></li>', $html);

                continue;
            }

            $this->assertStringContainsString(\sprintf('<a href="/posts?page=%d">%d</a>', $page, $page), $html);
        }
    }

    /**
     * @test
     */
    public function full_pager_windows_large_page_counts(): void
    {
        $html = $this->render('_full', new Page(collect(\range(1, 1000)), 50, 10), ['window' => 2]);

        $this->assertStringContainsString('<span>&hellip;</span>', $html);
        $this->assertStringContainsString('<a href="/posts?page=1">1</a>', $html);
        $this->assertStringContainsString('<a href="/posts?page=100">100</a>', $html);
        $this->assertStringContainsString('<a href="/posts?page=49">49</a>', $html);
        $this->assertStringNotContainsString('page=40', $html);
    }

    /**
     * @test
     */
    public function existing_query_parameters_are_kept(): void
    {
        $html = $this->render('_simple', new Page(collect(\range(1, 50)), 3, 10), [], '/posts?q=symfony&sort=-id');

        $this->assertStringContainsString('q=symfony', $html);
        $this->assertStringContainsString('sort=-id', $html);
        $this->assertStringContainsString('page=4', $html);
    }

    /**
     * @test
     */
    public function the_page_parameter_can_be_renamed(): void
    {
        $html = $this->render('_simple', new Page(collect(\range(1, 50)), 3, 10), ['key' => 'p']);

        $this->assertStringContainsString('/posts?p=4', $html);
    }

    /**
     * @param array<string,mixed> $context
     */
    private function render(string $template, Page $page, array $context = [], string $uri = '/posts'): string
    {
        $container = self::getContainer();
        $request = Request::create($uri);
        $request->attributes->set('_route', 'posts');

        $container->get('request_stack')->push($request);

        return $container->get('twig')->render(
            \sprintf('@ZenstruckCollection/Pager/%s.html.twig', $template),
            ['page' => $page] + $context,
        );
    }
}
