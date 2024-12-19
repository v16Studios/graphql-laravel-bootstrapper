<?php

namespace GraphQL\Bootstrapper\Traits;

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;

trait HasFieldPagination
{
    /**
     * Resolves the current page for pagination.
     */
    protected function resolveCurrentPageForPagination(?string $cursor): void
    {
        CursorPaginator::currentCursorResolver(function () use ($cursor) {
            return Cursor::fromEncoded($cursor ?? null);
        });
    }
}
