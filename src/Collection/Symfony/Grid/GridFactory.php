<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Symfony\Grid;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Collection\Grid;
use Zenstruck\Collection\Grid\GridBuilder;
use Zenstruck\Collection\Grid\GridDefinition;
use Zenstruck\Collection\Grid\Input\UriInput;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class GridFactory
{
    public function __construct(private ContainerInterface $definitions)
    {
    }

    /**
     * @return Grid<array<string,mixed>|object>
     */
    public function createFor(string $definition, string|Request $input, ?string $key = null): Grid
    {
        $definitionObject = $this->definitions->get($definition);

        if (!$definitionObject instanceof GridDefinition) {
            throw new \LogicException(\sprintf('Definition "%s" must be an instance of "%s".', $definition, GridDefinition::class));
        }

        $definitionObject->configure($builder = new GridBuilder());

        return $builder->build(new UriInput($input, $key));
    }
}
