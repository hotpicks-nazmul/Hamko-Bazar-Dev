<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FormBuilderController extends Controller
{
    /**
     * Display a listing of the forms.
     */
    public function index(Request $request)
    {
        $query = Form::query();
        
        // Search
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }
        
        // Status filter
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }
        
        // Sort
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);
        
        $forms = $query->paginate(25);
        
        // Stats
        $stats = [
            'total' => Form::count(),
            'active' => Form::where('is_active', true)->count(),
            'inactive' => Form::where('is_active', false)->count(),
            'total_submissions' => FormSubmission::count(),
        ];
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.form-builder.partials.table-rows', compact('forms'))->render(),
                'stats' => $stats,
            ]);
        }
        
        return view('admin.form-builder.index', compact('forms', 'stats'));
    }

    /**
     * Show the form for creating a new form.
     */
    public function create()
    {
        return view('admin.form-builder.create');
    }

    /**
     * Store a newly created form.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'success_message' => 'nullable|string',
            'submit_button_text' => 'nullable|string|max:100',
            'redirect_url' => 'nullable|url',
        ]);

        // Set default values for checkboxes
        $validated['is_active'] = $request->has('is_active');
        $validated['show_on_frontend'] = $request->has('show_on_frontend');

        $validated['slug'] = Str::slug($request->name);
        
        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Form::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $form = Form::create($validated);

        return redirect()->route('admin.form-builder.edit', $form->id)
            ->with('success', 'Form created successfully. Now add fields to your form.');
    }

    /**
     * Display the specified form.
     */
    public function show(Request $request, $id)
    {
        $form = Form::findOrFail($id);
        
        return view('admin.form-builder.show', compact('form'));
    }

    /**
     * Show the form for editing a form.
     */
    public function edit($id)
    {
        $form = Form::with('fields')->findOrFail($id);
        $fieldTypes = Form::getFieldTypes();
        
        return view('admin.form-builder.edit', compact('form', 'fieldTypes'));
    }

    /**
     * Update the specified form.
     */
    public function update(Request $request, $id)
    {
        $form = Form::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:forms,name,' . $id,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'success_message' => 'nullable|string',
            'submit_button_text' => 'nullable|string|max:100',
            'redirect_url' => 'nullable|url',
            'is_active' => 'boolean',
            'show_on_frontend' => 'boolean',
        ]);

        // Update slug only if name changed
        if ($form->name !== $request->name) {
            $validated['slug'] = Str::slug($request->name);
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Form::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $form->update($validated);

        return redirect()->back()->with('success', 'Form updated successfully.');
    }

    /**
     * Remove the specified form.
     */
    public function destroy($id)
    {
        $form = Form::findOrFail($id);
        $form->delete();

        return redirect()->route('admin.form-builder.index')
            ->with('success', 'Form deleted successfully.');
    }

    /**
     * Add a field to the form.
     */
    public function storeField(Request $request, $formId)
    {
        $form = Form::findOrFail($formId);
        
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(Form::getFieldTypes())),
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
            'width' => 'integer|min:1|max:12',
            'default_value' => 'nullable|string',
        ]);

        // Handle boolean fields manually (checkbox handling)
        $validated['is_required'] = $request->has('is_required');
        $validated['is_unique'] = $request->has('is_unique');
        $validated['is_visible'] = $request->has('is_visible');
        $validated['is_editable'] = $request->has('is_editable');

        // Generate field name from label
        $fieldName = Str::slug($validated['label'], '_');
        
        // Ensure unique name within form
        $originalName = $fieldName;
        $counter = 1;
        while (FormField::where('form_id', $formId)->where('name', $fieldName)->exists()) {
            $fieldName = $originalName . '_' . $counter;
            $counter++;
        }
        
        $validated['name'] = $fieldName;
        $validated['form_id'] = $formId;
        $validated['order'] = $form->fields()->max('order') + 1;
        
        // Model mutators handle JSON encoding, so we pass arrays directly
        if (isset($validated['options']) && is_string($validated['options'])) {
            $validated['options'] = json_decode($validated['options'], true);
        }
        
        if (isset($validated['validation_rules']) && is_string($validated['validation_rules'])) {
            $validated['validation_rules'] = json_decode($validated['validation_rules'], true);
        }

        $field = FormField::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Field added successfully.',
            'field' => $field,
        ]);
    }

    /**
     * Update a field.
     */
    public function updateField(Request $request, $formId, $fieldId)
    {
        $field = FormField::where('form_id', $formId)->findOrFail($fieldId);
        
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(Form::getFieldTypes())),
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
            'width' => 'integer|min:1|max:12',
            'default_value' => 'nullable|string',
        ]);

        // Handle boolean fields manually (checkbox handling)
        $validated['is_required'] = $request->has('is_required');
        $validated['is_unique'] = $request->has('is_unique');
        $validated['is_visible'] = $request->has('is_visible');
        $validated['is_editable'] = $request->has('is_editable');

        // Update name if label changed
        if ($field->label !== $request->label) {
            $fieldName = Str::slug($validated['label'], '_');
            
            // Ensure unique name within form
            $originalName = $fieldName;
            $counter = 1;
            while (FormField::where('form_id', $formId)->where('name', $fieldName)->where('id', '!=', $fieldId)->exists()) {
                $fieldName = $originalName . '_' . $counter;
                $counter++;
            }
            
            $validated['name'] = $fieldName;
        }

        // Model mutators handle JSON encoding, so we pass arrays directly
        if (isset($validated['options']) && is_string($validated['options'])) {
            $validated['options'] = json_decode($validated['options'], true);
        }
        
        if (isset($validated['validation_rules']) && is_string($validated['validation_rules'])) {
            $validated['validation_rules'] = json_decode($validated['validation_rules'], true);
        }

        $field->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Field updated successfully.',
            'field' => $field,
        ]);
    }

    /**
     * Get a field for editing (API endpoint).
     */
    public function getField($formId, $fieldId)
    {
        $field = FormField::where('form_id', $formId)->findOrFail($fieldId);
        
        // Return field with properly parsed options
        $fieldData = $field->toArray();
        
        // Parse options from JSON string to array for JavaScript
        if (is_string($field->options) && !empty($field->options)) {
            $fieldData['options'] = json_decode($field->options, true);
        } elseif (is_array($field->options)) {
            $fieldData['options'] = $field->options;
        } else {
            $fieldData['options'] = [];
        }
        
        return response()->json([
            'field' => $fieldData,
        ]);
    }

    /**
     * Delete a field.
     */
    public function destroyField($formId, $fieldId)
    {
        $field = FormField::where('form_id', $formId)->findOrFail($fieldId);
        $field->delete();

        return response()->json([
            'success' => true,
            'message' => 'Field deleted successfully.',
        ]);
    }

    /**
     * Reorder fields.
     */
    public function reorderFields(Request $request, $formId)
    {
        $request->validate([
            'fields' => 'required|array',
            'fields.*.id' => 'required|integer|exists:form_fields,id',
            'fields.*.order' => 'required|integer',
        ]);

        foreach ($request->fields as $fieldData) {
            FormField::where('id', $fieldData['id'])
                ->where('form_id', $formId)
                ->update(['order' => $fieldData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fields reordered successfully.',
        ]);
    }

    /**
     * Display form submissions.
     */
    public function submissions(Request $request, $formId)
    {
        $form = Form::findOrFail($formId);
        
        $query = $form->submissions();
        
        // Filter by read status
        if ($request->status === 'unread') {
            $query->where('is_read', false);
        } elseif ($request->status === 'read') {
            $query->where('is_read', true);
        }
        
        // Search in submission data
        if ($request->search) {
            $query->where('data', 'like', "%{$request->search}%");
        }
        
        $submissions = $query->paginate(25);

        return view('admin.form-builder.submissions', compact('form', 'submissions'));
    }

    /**
     * Display a specific submission.
     */
    public function showSubmission($formId, $submissionId)
    {
        $form = Form::findOrFail($formId);
        $submission = FormSubmission::where('form_id', $formId)->findOrFail($submissionId);
        
        // Mark as read
        if (!$submission->is_read) {
            $submission->markAsRead();
        }

        return view('admin.form-builder.submission-detail', compact('form', 'submission'));
    }

    /**
     * Delete a submission.
     */
    public function destroySubmission($formId, $submissionId)
    {
        $submission = FormSubmission::where('form_id', $formId)->findOrFail($submissionId);
        $submission->delete();

        // Decrement submissions count
        Form::findOrFail($formId)->decrement('submissions_count');

        return redirect()->back()->with('success', 'Submission deleted successfully.');
    }

    /**
     * Toggle form status.
     */
    public function toggleStatus($id)
    {
        $form = Form::findOrFail($id);
        $form->update(['is_active' => !$form->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $form->is_active,
            'message' => $form->is_active ? 'Form activated.' : 'Form deactivated.',
        ]);
    }

    /**
     * Duplicate a form.
     */
    public function duplicate($id)
    {
        $originalForm = Form::with('fields')->findOrFail($id);
        
        // Create new form
        $newForm = $originalForm->replicate();
        $newForm->name = $originalForm->name . ' (Copy)';
        $newForm->slug = Str::slug($newForm->name);
        $newForm->submissions_count = 0;
        
        // Ensure unique slug
        $originalSlug = $newForm->slug;
        $counter = 1;
        while (Form::where('slug', $newForm->slug)->exists()) {
            $newForm->slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        $newForm->save();
        
        // Copy fields
        foreach ($originalForm->fields as $field) {
            $newField = $field->replicate();
            $newField->form_id = $newForm->id;
            $newField->save();
        }

        return redirect()->route('admin.form-builder.edit', $newForm->id)
            ->with('success', 'Form duplicated successfully.');
    }

    /**
     * Export submissions.
     */
    public function exportSubmissions(Request $request, $formId)
    {
        $form = Form::findOrFail($formId);
        $submissions = $form->submissions()->get();
        
        $data = [];
        $headers = ['ID', 'Date', 'User'];
        
        // Get field labels as headers
        foreach ($form->fields as $field) {
            $headers[] = $field->label;
        }
        
        $data[] = $headers;
        
        foreach ($submissions as $submission) {
            $row = [
                $submission->id,
                $submission->created_at->format('Y-m-d H:i:s'),
                $submission->user_display_name,
            ];
            
            foreach ($form->fields as $field) {
                $value = $submission->getFieldValue($field->name);
                $row[] = is_array($value) ? implode(', ', $value) : $value;
            }
            
            $data[] = $row;
        }
        
        // Create CSV
        $filename = $form->slug . '_submissions_' . date('Y-m-d') . '.csv';
        
        $handle = fopen('php://temp', 'r+');
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Mark submission as read/unread.
     */
    public function toggleReadStatus($formId, $submissionId)
    {
        $submission = FormSubmission::where('form_id', $formId)->findOrFail($submissionId);
        $submission->update(['is_read' => !$submission->is_read]);

        return response()->json([
            'success' => true,
            'is_read' => $submission->is_read,
        ]);
    }

    /**
     * Add note to submission.
     */
    public function addNote(Request $request, $formId, $submissionId)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $submission = FormSubmission::where('form_id', $formId)->findOrFail($submissionId);
        $submission->update(['notes' => $request->notes]);

        return response()->json([
            'success' => true,
            'message' => 'Note added successfully.',
        ]);
    }
}
