<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    /**
     * Display a form by slug.
     */
    public function show($slug)
    {
        $form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();
        
        return view('frontend.forms.show', compact('form'));
    }

    /**
     * Submit a form.
     */
    public function submit(Request $request, $slug)
    {
        $form = Form::where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->firstOrFail();

        // Build validation rules
        $rules = [];
        $fieldNames = [];
        
        foreach ($form->fields as $field) {
            $fieldName = $field->name;
            $fieldNames[$fieldName] = $field->label;
            
            $fieldRules = [];
            
            // Required rule
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            
            // Type-specific validation
            switch ($field->type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'phone':
                case 'tel':
                    $fieldRules[] = 'string';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:5120'; // 5MB max
                    break;
            }
            
            // Add any custom validation rules
            $validationRules = $field->validation_rules_array;
            if (!empty($validationRules)) {
                foreach ($validationRules as $rule) {
                    if (is_string($rule)) {
                        $fieldRules[] = $rule;
                    } elseif (is_array($rule) && isset($rule['rule'])) {
                        $fieldName = $rule['rule'];
                        $params = $rule['params'] ?? [];
                        $fieldRules[] = $fieldName . ':' . implode(',', $params);
                    }
                }
            }
            
            $rules[$fieldName] = $fieldRules;
        }

        // Validate request
        $validator = Validator::make($request->all(), $rules, [], $fieldNames);
        
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Process form data
        $data = [];
        
        foreach ($form->fields as $field) {
            $fieldName = $field->name;
            
            if ($field->type === 'file') {
                // Handle file upload
                if ($request->hasFile($fieldName)) {
                    $file = $request->file($fieldName);
                    $path = $file->store('form-uploads', 'public');
                    $data[$fieldName] = $path;
                } else {
                    $data[$fieldName] = null;
                }
            } elseif (in_array($field->type, ['checkbox'])) {
                // Handle checkboxes (multiple values)
                $data[$fieldName] = $request->input($fieldName, []);
            } else {
                // Handle other inputs
                $data[$fieldName] = $request->input($fieldName);
            }
        }

        // Determine user type
        $userType = 'guest';
        $userId = null;
        $guestEmail = null;
        
        if (Auth::check()) {
            $userType = 'user';
            $userId = Auth::id();
        } else {
            // Try to find email field for guest
            foreach ($form->fields as $field) {
                if ($field->type === 'email' && !empty($data[$field->name])) {
                    $guestEmail = $data[$field->name];
                    break;
                }
            }
        }

        // Create submission
        $submission = FormSubmission::create([
            'form_id' => $form->id,
            'user_type' => $userType,
            'user_id' => $userId,
            'guest_email' => $guestEmail,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data' => json_encode($data),
        ]);

        // Increment submissions count
        $form->incrementSubmissions();

        // Handle redirect or show success message
        if ($form->redirect_url) {
            return redirect($form->redirect_url)->with('success', $form->success_message ?? 'Thank you for your submission!');
        }

        return back()->with('success', $form->success_message ?? 'Thank you for your submission!');
    }

    /**
     * List all active forms (for widget use).
     */
    public function list()
    {
        $forms = Form::active()
            ->frontend()
            ->with('fields')
            ->get();
        
        return response()->json($forms);
    }
}
