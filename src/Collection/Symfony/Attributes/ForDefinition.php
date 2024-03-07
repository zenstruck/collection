<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Symfony\Attributes;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class ForDefinition extends Autowire
{
    public function __construct(string $name, ?string $key = null)
    {
        parent::__construct(
            expression: \sprintf(
                'service(".zenstruck_collection.grid_factory").createFor("%s", service("request_stack").getCurrentRequest(), "%s")',
                \addslashes($name),
                $key,
            )
        );
    }
}
