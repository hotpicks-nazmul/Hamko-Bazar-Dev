<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;

/**
 * Bulk-action payloads.
 *
 * Several list screens build their selection in JavaScript and post it as `JSON.stringify([...])` into a
 * hidden input, which arrives as the string "[1,2]" — while the controller validates `ids => required|array`
 * and rejects exactly the payload the page sends. The result is a bulk action that looks wired (checkbox,
 * button, confirm dialog) and does nothing. Normalise here, then validate the array.
 */
trait NormalizesIdLists
{
    /**
     * @return array<int,int>
     */
    protected function idList(Request $request, string $key = 'ids'): array
    {
        $raw = $request->input($key);

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : explode(',', $raw);
        }

        return array_values(array_filter(array_map('intval', (array) $raw)));
    }

    /**
     * Put the normalised list back on the request so the usual `exists` rules can run on it.
     */
    protected function normalizeIdList(Request $request, string $key = 'ids'): array
    {
        $ids = $this->idList($request, $key);
        $request->merge([$key => $ids]);

        return $ids;
    }
}
