<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine;

use Zenstruck\Collection\Doctrine\ORM\Specification\AntiJoin;
use Zenstruck\Collection\Doctrine\ORM\Specification\Join;
use Zenstruck\Collection\Doctrine\Specification\Cache;
use Zenstruck\Collection\Doctrine\Specification\Delete;
use Zenstruck\Collection\Doctrine\Specification\Instance;
use Zenstruck\Collection\Doctrine\Specification\Unwritable;
use Zenstruck\Collection\Spec;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DoctrineSpec extends Spec
{
    public static function readonly(): Unwritable
    {
        return new Unwritable();
    }

    public static function delete(): Delete
    {
        return new Delete();
    }

    public static function cache(?int $lifetime = null, ?string $key = null): Cache
    {
        return new Cache($lifetime, $key);
    }

    /**
     * @param class-string $class
     */
    public static function instanceOf(string $class): Instance
    {
        return new Instance($class);
    }

    public static function innerJoin(string $field, ?string $alias = null): Join
    {
        return Join::inner($field, $alias);
    }

    public static function leftJoin(string $field, ?string $alias = null): Join
    {
        return Join::left($field, $alias);
    }

    public static function antiJoin(string $field): AntiJoin
    {
        return new AntiJoin($field);
    }
}
