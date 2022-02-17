<?php

use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Doctrine\ORM\Result\Deletable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @extends Result<V>
 */
final class ORMResult extends Result
{
    use Deletable;
}
