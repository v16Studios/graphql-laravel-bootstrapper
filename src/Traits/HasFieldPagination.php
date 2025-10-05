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
        CursorPaginator::currentCursorResolver(function ($cursorName = 'cursor') use ($cursor) {
            // Treat empty strings as null; only decode non-empty cursor values
            $encoded = $cursor ?: null;
            return Cursor::fromEncoded($encoded);
        });
    }
}
