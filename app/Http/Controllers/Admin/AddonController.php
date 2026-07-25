<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddonController extends Controller
{
    use SortsLists;

    /**
     * Get available addon templates data.
     */
    private function getAddonTemplates(): array
    {
        return [
            'social-login' => [
                'name' => 'Social Login',
                'description' => 'Allow customers to login via social media platforms.',
                'version' => '1.0.0',
                'author' => 'Hamko Ecommerce',
                'icon' => 'bi bi-person-bounding-box',
            ],
            'multi-vendor' => [
                'name' => 'Multi-Vendor',
                'description' => 'Enable multi-vendor marketplace functionality.',
                'version' => '1.0.0',
                'author' => 'Hamko Ecommerce',
                'icon' => 'bi bi-shuffle',
            ],
            'live-chat' => [
                'name' => 'Live Chat',
                'description' => 'Add live chat support to your store.',
                'version' => '1.0.0',
                'author' => 'Hamko Ecommerce',
                'icon' => 'bi bi-chat-dots',
            ],
            'pos' => [
                'name' => 'Point of Sale',
                'description' => 'Enable POS system for offline sales.',
                'version' => '1.0.0',
                'author' => 'Hamko Ecommerce',
                'icon' => 'bi bi-cash-stack',
            ],
            'reviews-pro' => [
                'name' => 'Product Reviews Pro',
                'description' => 'Advanced product review system with ratings and photos.',
                'version' => '1.0.0',
                'author' => 'Hamko Ecommerce',
                'icon' => 'bi bi-star',
            ],
            'seo-booster' => [
                'name' => 'SEO Booster',
                'description' => 'Advanced SEO tools to boost your search rankings.',
                'version' => '1.0.0',
                'author' => 'Hamko Ecommerce',
                'icon' => 'bi bi-search',
            ],
        ];
    }

    /**
     * A slug nothing else holds: the base form, then -2, -3 … The UNIQUE index on `addons.slug` made a
     * rename onto a taken slug a 500 rather than a save.
     */
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'addon';
        $slug = $base;
        $suffix = 2;

        while (Addon::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    /**
     * Display a listing of the addons.
     */
    public function index(Request $request)
    {
        $query = Addon::query();

        // Filter by status — an allow-listed value, so `?status=nonsense` filters nothing instead of
        // asking the database for a status the enum cannot hold.
        if (in_array($request->status, ['active', 'inactive', 'uninstalled'], true)) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%")
                    ->orWhere('author', 'like', "%{$request->search}%");
            });
        }

        // Sorting. `?direction=sideways` reached orderBy() and threw InvalidArgumentException ("Order
        // direction must be asc or desc") — a 500 on the list. `?sort=nope` only survived because the
        // table is empty: Laravel skips the items query when the count is zero, so it would 500 the
        // moment one addon existed.
        $sort = $this->sortColumn($request, ['name', 'slug', 'version', 'author', 'status', 'is_core', 'sort_order', 'created_at'], 'sort_order');
        $direction = $this->sortDirection($request, 'asc');
        $query->orderBy($sort, $direction);

        $addons = $query->paginate($this->perPage($request, 15, 100));

        // Stats
        $stats = [
            'total' => Addon::count(),
            'active' => Addon::where('status', 'active')->count(),
            'inactive' => Addon::where('status', 'inactive')->count(),
            'uninstalled' => Addon::where('status', 'uninstalled')->count(),
        ];

        return view('admin.addons.index', compact('addons', 'stats'));
    }

    /**
     * Show the form for installing a new addon.
     */
    public function install()
    {
        return view('admin.addons.install');
    }

    /**
     * Process the installation of a new addon.
     */
    public function processInstall(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:addons,name',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'author' => 'nullable|string|max:255',
            'author_website' => 'nullable|string|url',
            'website' => 'nullable|string|url',
            'icon' => 'nullable|string|max:100',
            'is_core' => 'nullable|boolean',
        ]);

        // $request->all() fed the model directly, so any fillable column the request happened to carry
        // (settings, sort_order, installed_at) was written verbatim. Named fields only.
        $data = $request->only(['name', 'description', 'version', 'author', 'author_website', 'website', 'icon']);
        $data['slug'] = $this->uniqueSlug($request->name);
        $data['status'] = 'inactive';
        // is_core is a developer flag, not a panel checkbox: a ticked box used to create an addon that
        // could never be activated, deactivated or deleted (see the core guards on every action).
        $data['is_core'] = false;

        Addon::create($data);

        return redirect()->route('admin.addons.index')
            ->with('success', 'Addon installed successfully. You can now activate it.');
    }

    /**
     * Show the form for editing the specified addon.
     */
    public function edit($id)
    {
        $addon = Addon::findOrFail($id);
        return view('admin.addons.edit', compact('addon'));
    }

    /**
     * Update the specified addon in storage.
     */
    public function update(Request $request, $id)
    {
        $addon = Addon::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:addons,name,' . $id,
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'author' => 'nullable|string|max:255',
            'author_website' => 'nullable|string|url',
            'website' => 'nullable|string|url',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_core' => 'nullable|boolean',
        ]);

        $data = $request->only(['name', 'description', 'version', 'author', 'author_website', 'website', 'icon', 'sort_order']);

        if ($request->filled('name') && $addon->name !== $request->name) {
            // Renaming used to set the slug outright: two different names ("Probe-Slug Collide" and
            // "Probe Slug Collide") collapse to the same slug and the UNIQUE index answered SQLSTATE 23000
            // 1062 — a 500 on save. Derive a free one instead.
            $data['slug'] = $this->uniqueSlug($request->name, $addon->id);
        }

        // is_core stays whatever it already is; only a developer flips that in the database.

        if ($request->has('settings')) {
            $settingsJson = $request->input('settings');
            $data['settings'] = !empty($settingsJson) ? json_decode($settingsJson, true) : [];
            if (json_last_error() !== JSON_ERROR_NONE && !empty($settingsJson)) {
                return redirect()->back()->withInput()->with('error', 'Invalid JSON format in settings field.');
            }
        }

        $addon->update($data);

        return redirect()->route('admin.addons.index')
            ->with('success', 'Addon updated successfully.');
    }

    /**
     * Activate the specified addon.
     */
    public function activate($id)
    {
        $addon = Addon::findOrFail($id);

        if ($addon->is_core) {
            return redirect()->back()->with('error', 'Core addons cannot be activated or deactivated.');
        }

        $addon->activate();

        return redirect()->back()
            ->with('success', $addon->name . ' activated successfully.');
    }

    /**
     * Deactivate the specified addon.
     */
    public function deactivate($id)
    {
        $addon = Addon::findOrFail($id);

        if ($addon->is_core) {
            return redirect()->back()->with('error', 'Core addons cannot be activated or deactivated.');
        }

        $addon->deactivate();

        return redirect()->back()
            ->with('success', $addon->name . ' deactivated successfully.');
    }

    /**
     * Toggle the status of the addon.
     */
    public function toggleStatus($id)
    {
        $addon = Addon::findOrFail($id);

        if ($addon->is_core) {
            return redirect()->back()->with('error', 'Core addons cannot be activated or deactivated.');
        }

        if ($addon->isActive()) {
            $addon->deactivate();
            $message = $addon->name . ' deactivated successfully.';
        } else {
            $addon->activate();
            $message = $addon->name . ' activated successfully.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove the specified addon from storage.
     */
    public function destroy($id)
    {
        $addon = Addon::findOrFail($id);

        if ($addon->is_core) {
            return redirect()->back()->with('error', 'Core addons cannot be uninstalled.');
        }

        $addon->delete();

        return redirect()->route('admin.addons.index')
            ->with('success', 'Addon removed successfully.');
    }

    /**
     * Update addon settings.
     */
    public function settings(Request $request, $id)
    {
        $addon = Addon::findOrFail($id);

        $request->validate([
            'settings' => 'nullable|array',
        ]);

        $addon->update(['settings' => $request->settings ?? []]);

        return redirect()->back()->with('success', 'Addon settings updated successfully.');
    }

    /**
     * Bulk action on addons.
     */
    public function bulkAction(Request $request)
    {
        $ids = $request->ids;

        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'No addons selected.');
        }

        $request->merge(['ids' => $ids]);

        $request->validate([
            'action' => 'required|string|in:activate,deactivate,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:addons,id',
        ]);

        $addons = Addon::whereIn('id', $request->ids)->get();
        $count = 0;
        $skipped = 0;

        foreach ($addons as $addon) {
            // The single-record routes refuse every action on a core addon. Bulk used to exempt
            // `activate` from that rule, so the same set of rows behaved differently depending on how you
            // selected them.
            if ($addon->is_core) {
                $skipped++;

                continue;
            }

            switch ($request->action) {
                case 'activate':
                    $addon->activate();
                    $count++;
                    break;
                case 'deactivate':
                    if (!$addon->is_core) {
                        $addon->deactivate();
                        $count++;
                    }
                    break;
                case 'delete':
                    if (!$addon->is_core) {
                        $addon->delete();
                        $count++;
                    }
                    break;
            }
        }

        $message = match ($request->action) {
            'activate' => "{$count} addon(s) activated successfully.",
            'deactivate' => "{$count} addon(s) deactivated successfully.",
            'delete' => "{$count} addon(s) deleted successfully.",
            default => 'Action completed.',
        };

        if ($skipped > 0) {
            $message .= " {$skipped} core addon(s) were skipped — core addons cannot be activated, deactivated or removed.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Reorder addons.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:addons,id',
        ]);

        foreach ($request->order as $index => $id) {
            Addon::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true, 'message' => 'Addons reordered successfully.']);
    }

    /**
     * Get available addon templates (for demo - can be extended to fetch from remote).
     */
    public function templates()
    {
        $templatesData = $this->getAddonTemplates();
        $templates = collect($templatesData)->map(function ($data, $slug) {
            $data['slug'] = $slug;
            return (object) $data;
        })->values();

        return response()->json(['templates' => $templates]);
    }

    /**
     * Install from template.
     */
    public function installFromTemplate(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
        ]);

        $templates = $this->getAddonTemplates();
        $slug = $request->slug;

        if (!array_key_exists($slug, $templates)) {
            return redirect()->back()->with('error', 'Invalid addon template.');
        }

        $existing = Addon::where('slug', $slug)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'This addon is already installed.');
        }

        $data = $templates[$slug];
        $data['slug'] = $slug;
        $data['status'] = 'inactive';
        $data['is_core'] = false;

        Addon::create($data);

        return redirect()->route('admin.addons.index')
            ->with('success', $data['name'] . ' installed successfully. You can now activate it.');
    }
}
