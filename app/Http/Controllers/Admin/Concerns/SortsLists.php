<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;

/**
 * Sort and page-size handling for admin list screens.
 *
 * Every affiliate list fed `$request->sort` straight into `orderBy()` and `$request->per_page` into
 * `paginate()`. `?sort=nope` was a SQLSTATE 42S22 on six screens, `?direction=sideways` a syntax
 * error, and `?per_page=abc` a 500. Nothing here trusts the query string: the column must be in the
 * caller's whitelist, the direction is one of two words, and the page size is clamped.
 */
trait SortsLists
{
    protected function sortColumn(Request $request, array $allowed, string $default = 'created_at', string $param = 'sort'): string
    {
        // $param lets a screen keep its own query-string name (`sort_by` on the staff lists) without
        // passing the raw value to orderBy().
        $sort = (string) $request->input($param, '');

        return in_array($sort, $allowed, true) ? $sort : $default;
    }

    protected function sortDirection(Request $request, string $default = 'desc', string $param = 'direction'): string
    {
        // Some lists (locations) are naturally ascending, so the caller can pick the fallback; anything
        // that is not literally "asc" stays descending rather than reaching the query builder.
        return strtolower((string) $request->input($param, $default)) === 'asc' ? 'asc' : 'desc';
    }

    /**
     * Query strings are text. `?search[]=x` arrives as an array, and every list blade echoes
     * `request('search')` into an input value, where htmlspecialchars() throws on an array — a 500 from a
     * hand-edited URL. Call this at the top of a list action to flatten it.
     */
    protected function sanitizeSearch(Request $request, string $param = 'search'): void
    {
        if ($request->query($param) !== null && ! is_string($request->query($param))) {
            $request->merge([$param => '']);
        }
    }

    protected function perPage(Request $request, int $default = 15, int $max = 100): int
    {
        $raw = $request->input('per_page');

        if (! is_numeric($raw)) {
            return $default;
        }

        $value = (int) $raw;

        if ($value < 1) {
            return $default;
        }

        return min($value, $max);
    }
}
