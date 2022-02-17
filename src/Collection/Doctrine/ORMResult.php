<?php

namespace Zenstruck\Collection\Doctrine;

use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Doctrine\ORM\Result\Deletable;

/**
 * General purpose {@see Result}.
 *
 * - Deletable
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @extends Result<V>
 */
class ORMResult extends Result
{
    use Deletable;
}
