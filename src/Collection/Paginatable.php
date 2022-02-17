<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 */
trait Paginatable
{
    /**
     * @return Page<V>
     */
    public function paginate(int $page = 1, int $limit = Page::DEFAULT_LIMIT): Page
    {
        return $this->pages($limit)->get($page);
    }

    /**
     * @return PageCollection<V>
     */
    public function pages(int $limit = Page::DEFAULT_LIMIT): PageCollection
    {
        if (!$this instanceof Collection) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, Collection::class));
        }

        return new PageCollection($this, $limit);
    }
}
