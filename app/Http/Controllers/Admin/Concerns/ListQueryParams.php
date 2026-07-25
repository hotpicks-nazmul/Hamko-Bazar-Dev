<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;

/**
 * Query-string hardening shared by the admin list screens.
 *
 * Every one of these lists used to pass `?sort=`, `?direction=` and `?per_page=` straight into
 * orderBy()/paginate(): an unknown column answered 500, the direction was injectable, and
 * per_page=-5 blew up inside the paginator maths while per_page=abc reached the query builder.
 */
trait ListQueryParams
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array<int, string>  $allowed  columns the screen is allowed to sort by
     */
    protected function applySort($query, Request $request, array $allowed): void
    {
        $sort = $request->get('sort', 'created_at');
        $direction = strtolower((string) $request->get('direction', 'desc'));

        if (! in_array($sort, $allowed, true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction);
    }

    /**
     * @param  array<int, int>  $allowed
     */
    protected function perPage(Request $request, array $allowed = [10, 25, 50, 100]): int
    {
        $perPage = (int) $request->get('per_page', 25);

        return in_array($perPage, $allowed, true) ? $perPage : 25;
    }
}
