<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WidgetController extends Controller
{
    /**
     * Display a listing of the widgets.
     */
    public function index(Request $request)
    {
        $sort = $request->sort ?? 'sort_order';
        $direction = $request->direction ?? 'asc';
        
        $widgets = Widget::when($request->search, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%")
                  ->orWhere('widget_type', 'like', "%{$request->search}%");
            });
        })
        ->when($request->widget_type, function ($query) use ($request) {
            $query->where('widget_type', $request->widget_type);
        })
        ->when($request->status, function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->orderBy($sort, $direction)
        ->paginate(15);

        $widgetTypes = Widget::getWidgetTypes();

        return view('admin.content.widgets.index', compact('widgets', 'widgetTypes'));
    }

    /**
     * Show the form for creating a new widget.
     */
    public function create()
    {
        $widgetTypes = Widget::getWidgetTypes();
        $categories = Category::where('parent_id', 0)
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('admin.content.widgets.create', compact('widgetTypes', 'categories'));
    }

    /**
     * Store a newly created widget in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:widgets,name',
            'widget_type' => 'required|string|in:' . implode(',', array_keys(Widget::getWidgetTypes())),
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'settings' => 'nullable|array',
            'category_id' => 'nullable|exists:categories,id',
            'product_limit' => 'nullable|integer|min:1|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        
        // Handle settings - model casts as array, so pass the array directly
        if ($request->has('settings') && is_array($request->settings)) {
            $data['settings'] = $request->settings;
        }

        Widget::create($data);

        return redirect()->route('admin.content.widgets.index')
            ->with('success', 'Widget created successfully.');
    }

    /**
     * Show the form for editing the specified widget.
     */
    public function edit($id)
    {
        $widget = Widget::findOrFail($id);
        $widgetTypes = Widget::getWidgetTypes();
        $categories = Category::where('parent_id', 0)
            ->with('children')
            ->orderBy('name')
            ->get();

        return view('admin.content.widgets.edit', compact('widget', 'widgetTypes', 'categories'));
    }

    /**
     * Update the specified widget in storage.
     */
    public function update(Request $request, $id)
    {
        $widget = Widget::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:widgets,name,' . $id,
            'widget_type' => 'required|string|in:' . implode(',', array_keys(Widget::getWidgetTypes())),
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'settings' => 'nullable|array',
            'category_id' => 'nullable|exists:categories,id',
            'product_limit' => 'nullable|integer|min:1|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'nullable|boolean',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        
        // Handle settings - model casts as array, so pass the array directly
        if ($request->has('settings') && is_array($request->settings)) {
            $data['settings'] = $request->settings;
        }

        $widget->update($data);

        return redirect()->route('admin.content.widgets.index')
            ->with('success', 'Widget updated successfully.');
    }

    /**
     * Remove the specified widget from storage.
     */
    public function destroy($id)
    {
        $widget = Widget::findOrFail($id);
        $widget->delete();

        return redirect()->route('admin.content.widgets.index')
            ->with('success', 'Widget deleted successfully.');
    }

    /**
     * Toggle the status of the widget.
     */
    public function toggleStatus($id)
    {
        $widget = Widget::findOrFail($id);
        
        $newStatus = $widget->status === 'active' ? 'inactive' : 'active';
        $widget->update(['status' => $newStatus]);

        return back()->with('success', 'Widget status updated.');
    }

    /**
     * Toggle the featured status of the widget.
     */
    public function toggleFeatured($id)
    {
        $widget = Widget::findOrFail($id);
        
        $widget->update(['is_featured' => !$widget->is_featured]);

        return back()->with('success', 'Widget featured status updated.');
    }

    /**
     * Reorder widgets via drag and drop.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'widgets' => 'required|array',
            'widgets.*.id' => 'required|exists:widgets,id',
            'widgets.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->widgets as $widgetData) {
            Widget::where('id', $widgetData['id'])->update([
                'sort_order' => $widgetData['sort_order']
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Widgets reordered successfully.']);
    }

    /**
     * Bulk action on widgets.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:activate,deactivate,delete,feature,unfeature',
            'widgets' => 'required|string',
        ]);

        // Parse comma-separated widget IDs
        $widgetIds = array_filter(array_map('intval', explode(',', $request->widgets)));
        
        $widgets = Widget::whereIn('id', $widgetIds);

        switch ($request->action) {
            case 'activate':
                $widgets->update(['status' => 'active']);
                $message = 'Selected widgets have been activated.';
                break;
            case 'deactivate':
                $widgets->update(['status' => 'inactive']);
                $message = 'Selected widgets have been deactivated.';
                break;
            case 'feature':
                $widgets->update(['is_featured' => true]);
                $message = 'Selected widgets have been marked as featured.';
                break;
            case 'unfeature':
                $widgets->update(['is_featured' => false]);
                $message = 'Selected widgets have been unmarked as featured.';
                break;
            case 'delete':
                $widgets->delete();
                $message = 'Selected widgets have been deleted.';
                break;
            default:
                return back()->with('error', 'Invalid action.');
        }

        return back()->with('success', $message);
    }

    /**
     * Get widgets for AJAX request (for frontend or preview).
     */
    public function getActiveWidgets()
    {
        $widgets = Widget::active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'widgets' => $widgets
        ]);
    }
}
