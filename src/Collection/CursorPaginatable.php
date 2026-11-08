<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @template K of array-key = array-key
 */
interface CursorPaginatable
{
    /**
     * Pass null to fetch the first page.
     *
     * @param positive-int $limit
     *
     * @return CursorPage<V,K>
     */
    public function cursorPaginate(?string $cursor = null, int $limit = Page::DEFAULT_LIMIT): CursorPage;
}
