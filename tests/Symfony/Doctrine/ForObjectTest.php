<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Symfony\Doctrine;

use Composer\InstalledVersions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Zenstruck\Collection\Symfony\Doctrine\ForObject;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ForObjectTest extends TestCase
{
    /**
     * @test
     */
    public function less_than_63(): void
    {
        if (\version_compare(InstalledVersions::getVersion('symfony/dependency-injection'), '6.3.0', '>=')) {
            $this->markTestSkipped('Symfony >= 6.3.0');
        }

        $this->expectException(\LogicException::class);

        new ForObject(\stdClass::class);
    }

    /**
     * @test
     */
    public function greater_than_63(): void
    {
        if (\version_compare(InstalledVersions::getVersion('symfony/dependency-injection'), '6.3.0', '<')) {
            $this->markTestSkipped('Symfony < 6.3.0');
        }

        $attribute = new ForObject(\stdClass::class);

        $this->assertInstanceOf(Autowire::class, $attribute);
        $this->assertSame(\stdClass::class, $attribute->class);
    }
}
