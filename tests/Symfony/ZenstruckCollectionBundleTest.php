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

use Composer\InstalledVersions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Collection\Tests\Symfony\Fixture\Entity\Category;
use Zenstruck\Collection\Tests\Symfony\Fixture\Entity\Post;
use Zenstruck\Collection\Tests\Symfony\Fixture\Service1;
use Zenstruck\Collection\Tests\Symfony\Fixture\Service2;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

use function Zenstruck\Foundry\create;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ZenstruckCollectionBundleTest extends KernelTestCase
{
    use Factories, ResetDatabase;

    /**
     * @test
     */
    public function autowiring(): void
    {
        create(Post::class, ['id' => 1]);
        create(Category::class, ['id' => 2]);

        /** @var Service1 $service */
        $service = self::getContainer()->get(Service1::class);

        $this->assertInstanceOf(Post::class, $service->factory->create(Post::class)->find(1));
        $this->assertInstanceOf(Post::class, $service->postRepo->find(1));
        $this->assertInstanceOf(Category::class, $service->factory->create(Category::class)->find(2));
        $this->assertSame($service->factory->create(Category::class), $service->factory->create(Category::class));
    }

    /**
     * @test
     */
    public function autowiring_for_object(): void
    {
        if (\version_compare(InstalledVersions::getVersion('symfony/dependency-injection'), '6.3.0', '<')) {
            $this->markTestSkipped('symfony/dependency-injection 6.3.0+ required.');
        }

        create(Category::class, ['id' => 2]);

        /** @var Service1 $service1 */
        $service1 = self::getContainer()->get(Service1::class);

        /** @var Service2 $service2 */
        $service2 = self::getContainer()->get(Service2::class);

        $this->assertInstanceOf(Category::class, $service2->categoryRepo->find(2));
        $this->assertSame($service2->categoryRepo, $service1->factory->create(Category::class));
    }
}
