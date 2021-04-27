<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Fixture;

use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Doctrine\ORM\Result\Deletable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class KitchenSinkResult extends Result
{
    use Deletable;
}
