<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;
use Zenstruck\Collection\Tests\PagintableCollectionTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class ResultTest extends TestCase
{
    use HasDatabase, PagintableCollectionTests;

    abstract protected function createWithItems(int $count): Result;
}
