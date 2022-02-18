<?php

namespace Zenstruck\Collection\Doctrine\ORM\Specification;

use Zenstruck\Collection\Specification\Field;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AntiJoin extends Field
{
    public function __toString(): string
    {
        return \sprintf('AntiJoin(%s)', $this->field());
    }
}
