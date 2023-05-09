<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Symfony\Doctrine;

use Composer\InstalledVersions;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\ExpressionLanguage\Expression;

$version = InstalledVersions::getVersion('symfony/dependency-injection');

if ($version && \class_exists(Expression::class) && \version_compare($version, '6.3.0', '>=')) {
    /**
     * @author Kevin Bond <kevinbond@gmail.com>
     */
    #[\Attribute(\Attribute::TARGET_PARAMETER)]
    final class ForObject extends Autowire
    {
        public function __construct(
            /**
             * @readonly
             *
             * @var class-string $class
             */
            public string $class,
        ) {
            parent::__construct(
                expression: \sprintf('service("zenstruck_collection.doctrine.chain_object_repo_factory").create("%s")', \addslashes($this->class)),
            );
        }
    }
} else {
    /**
     * @author Kevin Bond <kevinbond@gmail.com>
     */
    #[\Attribute(\Attribute::TARGET_PARAMETER)]
    final class ForObject
    {
        public function __construct(
            /**
             * @readonly
             *
             * @var class-string $class
             */
            public string $class,
        ) {
            throw new \LogicException(\sprintf('The %s attribute requires symfony/dependency-injection 6.3+ and symfony/expression-language.', self::class));
        }
    }
}
