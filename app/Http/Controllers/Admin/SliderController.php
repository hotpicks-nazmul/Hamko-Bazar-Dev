<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Helpers\ImageHelper;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        $query = Slider::query();
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('subtitle', 'like', "%{$request->search}%");
            });
        }
        
        // Status filter
        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', (bool) $request->status);
        }
        
        $sliders = $query->orderBy('order')->paginate(25);
        
        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.sliders.partials.slider-rows', compact('sliders'))->render(),
            ]);
        }
        
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'required|image|max:5120',
            'link' => 'nullable|url|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_color' => 'nullable|string|max:20',
            'button_text_color' => 'nullable|string|max:20',
            'button_icon' => 'nullable|string|max:50',
            'button_icon_color' => 'nullable|string|max:20',
        ]);

        $data = $request->only([
            'title', 'subtitle', 'link', 'button_text', 
            'button_color', 'button_text_color', 'button_icon', 'button_icon_color'
        ]);
        
        // Handle image upload with ImageHelper
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'sliders',
                    1920,
                    300,
                    85
                );
                $data['image'] = $imageResult['path'];
                $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }
        
        $data['order'] = Slider::max('order') + 1;
        $data['is_active'] = $request->has('is_active');

        Slider::create($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider created successfully.');
    }

    public function show(Slider $slider)
    {
        return view('admin.sliders.show', compact('slider'));
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120',
            'link' => 'nullable|url|max:255',
            'button_text' => 'nullable|string|max:50',
            'button_color' => 'nullable|string|max:20',
            'button_text_color' => 'nullable|string|max:20',
            'button_icon' => 'nullable|string|max:50',
            'button_icon_color' => 'nullable|string|max:20',
        ]);

        $data = $request->only([
            'title', 'subtitle', 'link', 'button_text', 
            'button_color', 'button_text_color', 'button_icon', 'button_icon_color'
        ]);
        
        // Handle image upload with ImageHelper
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'sliders',
                    1920,
                    300,
                    85
                );
                $data['image'] = $imageResult['path'];
                $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }
        
        $data['is_active'] = $request->has('is_active');

        $slider->update($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider updated successfully.');
    }

    public function destroy(Slider $slider)
    {
        // Delete image files
        if ($slider->image) {
            ImageHelper::deleteImage($slider->image, $slider->thumbnail ?? null);
        }
        
        $slider->delete();
        return back()->with('success', 'Slider deleted successfully.');
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $index => $id) {
            Slider::where('id', $id)->update(['order' => $index]);
        }
        return response()->json(['success' => true]);
    }
}
