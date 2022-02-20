<?php

namespace Zenstruck\Collection\Doctrine\Specification;

use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Doctrine\ORM\QueryBuilder as ORMQueryBuilder;
use Zenstruck\Collection\Doctrine\Specification\Interpreter\CallableInterpreter;
use Zenstruck\Collection\Doctrine\Specification\Interpreter\ComparisonInterpreter;
use Zenstruck\Collection\Doctrine\Specification\Interpreter\CompositeInterpreter;
use Zenstruck\Collection\Doctrine\Specification\Interpreter\NullInterpreter;
use Zenstruck\Collection\Doctrine\Specification\Interpreter\OrderByInterpreter;
use Zenstruck\Collection\Specification\Interpreter;
use Zenstruck\Collection\Specification\Interpreter\NestedInterpreter;
use Zenstruck\Collection\Specification\SpecificationInterpreter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Context
{
    /** @var array<class-string,SpecificationInterpreter> */
    private static array $defaultInterpreter = [];

    private string $alias;

    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    final public function alias(): string
    {
        return $this->alias;
    }

    final public function prefixAlias(string $value): string
    {
        return "{$this->alias}.{$value}";
    }

    abstract public function qb(): ORMQueryBuilder|DBALQueryBuilder;

    abstract public function scopeTo(string $alias): self;

    final public static function defaultInterpreter(): SpecificationInterpreter
    {
        return self::$defaultInterpreter[static::class] ??= new SpecificationInterpreter(static::defaultInterpreters());
    }

    /**
     * @return Interpreter[]
     */
    protected static function defaultInterpreters(): array
    {
        return [
            new NestedInterpreter(),
            new CallableInterpreter(),
            new ComparisonInterpreter(),
            new CompositeInterpreter(),
            new NullInterpreter(),
            new OrderByInterpreter(),
        ];
    }
}
