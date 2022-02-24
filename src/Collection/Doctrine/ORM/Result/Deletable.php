<?php

namespace Zenstruck\Collection\Doctrine\ORM\Result;

use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Zenstruck\Collection\Doctrine\ORM\EntityWithAggregates;
use Zenstruck\Collection\Doctrine\ORM\Result;

/**
 * Enables your results to be deleted from the db.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait Deletable
{
    /**
     * @param callable(object):void|null $callback
     */
    final public function delete(?callable $callback = null): int
    {
        if (!$this instanceof Result) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, Result::class));
        }

        $callback ??= static function() {};
        $count = 0;

        foreach ($this->batchProcess() as $entity) {
            $entity = $entity instanceof EntityWithAggregates ? $entity->entity() : $entity;

            try {
                $this->em()->remove($entity);
            } catch (ORMInvalidArgumentException|MappingException $e) {
                throw new \LogicException('Can only delete results of the managed object.', 0, $e);
            }

            $callback($entity);
            ++$count;
        }

        $this->resetCount();

        return $count;
    }
}
