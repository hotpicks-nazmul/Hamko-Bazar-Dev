@extends('themes.general.layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @if($form->title)
                    <h2 class="mb-3">{{ $form->title }}</h2>
                    @endif
                    
                    @if($form->description)
                    <p class="text-muted mb-4">{{ $form->description }}</p>
                    @endif
                    
                    @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                    @endif
                    
                    <form method="POST" action="{{ route('forms.submit', $form->slug) }}" enctype="multipart/form-data">
                        @csrf
                        
                        @if($form->fields->count() > 0)
                        <div class="row">
                            @foreach($form->fields->sortBy('order') as $field)
                            <div class="mb-3" @if($field->width < 12) style="width: {{ ($field->width / 12) * 100 }}%" @endif>
                                <label class="form-label">
                                    {{ $field->label }}
                                    @if($field->is_required)
                                    <span class="text-danger">*</span>
                                    @endif
                                </label>
                                
                                @switch($field->type)
                                    @case('textarea')
                                        <textarea 
                                            name="{{ $field->name }}" 
                                            class="form-control @error($field->name) is-invalid @enderror"
                                            placeholder="{{ $field->placeholder }}"
                                            rows="4"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >{{ old($field->name) }}</textarea>
                                        @break
                                        
                                    @case('select')
                                        <select 
                                            name="{{ $field->name }}" 
                                            class="form-select @error($field->name) is-invalid @enderror"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                            <option value="">{{ $field->placeholder ?: 'Select an option' }}</option>
                                            @foreach($field->options_array as $option)
                                            <option value="{{ $option }}" {{ old($field->name) == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @break
                                        
                                    @case('radio')
                                        @foreach($field->options_array as $option)
                                        <div class="form-check">
                                            <input 
                                                type="radio" 
                                                name="{{ $field->name }}" 
                                                id="{{ $field->name }}_{{ $loop->index }}"
                                                value="{{ $option }}"
                                                class="form-check-input @error($field->name) is-invalid @enderror"
                                                {{ old($field->name) == $option ? 'checked' : '' }}
                                                {{ $field->is_required ? 'required' : '' }}
                                            >
                                            <label class="form-check-label" for="{{ $field->name }}_{{ $loop->index }}">
                                                {{ $option }}
                                            </label>
                                        </div>
                                        @endforeach
                                        @break
                                        
                                    @case('checkbox')
                                        @foreach($field->options_array as $option)
                                        <div class="form-check">
                                            <input 
                                                type="checkbox" 
                                                name="{{ $field->name }}[]" 
                                                id="{{ $field->name }}_{{ $loop->index }}"
                                                value="{{ $option }}"
                                                class="form-check-input @error($field->name) is-invalid @enderror"
                                                {{ in_array($option, old($field->name, [])) ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="{{ $field->name }}_{{ $loop->index }}">
                                                {{ $option }}
                                            </label>
                                        </div>
                                        @endforeach
                                        @break
                                        
                                    @case('file')
                                        <input 
                                            type="file" 
                                            name="{{ $field->name }}" 
                                            class="form-control @error($field->name) is-invalid @enderror"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                        @break
                                        
                                    @case('date')
                                        <input 
                                            type="date" 
                                            name="{{ $field->name }}" 
                                            class="form-control @error($field->name) is-invalid @enderror"
                                            placeholder="{{ $field->placeholder }}"
                                            value="{{ old($field->name) }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                        @break
                                        
                                    @case('time')
                                        <input 
                                            type="time" 
                                            name="{{ $field->name }}" 
                                            class="form-control @error($field->name) is-invalid @enderror"
                                            placeholder="{{ $field->placeholder }}"
                                            value="{{ old($field->name) }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                        @break
                                        
                                    @case('datetime')
                                        <input 
                                            type="datetime-local" 
                                            name="{{ $field->name }}" 
                                            class="form-control @error($field->name) is-invalid @enderror"
                                            placeholder="{{ $field->placeholder }}"
                                            value="{{ old($field->name) }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                        @break
                                        
                                    @case('number')
                                        <input 
                                            type="number" 
                                            name="{{ $field->name }}" 
                                            class="form-control @error($field->name) is-invalid @enderror"
                                            placeholder="{{ $field->placeholder }}"
                                            value="{{ old($field->name) }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                        @break
                                        
                                    @case('color')
                                        <input 
                                            type="color" 
                                            name="{{ $field->name }}" 
                                            class="form-control form-control-color @error($field->name) is-invalid @enderror"
                                            value="{{ old($field->name, $field->default_value) }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                        @break
                                        
                                    @case('range')
                                        <input 
                                            type="range" 
                                            name="{{ $field->name }}" 
                                            class="form-range @error($field->name) is-invalid @enderror"
                                            value="{{ old($field->name, $field->default_value) }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                        @break
                                        
                                    @case('hidden')
                                        <input 
                                            type="hidden" 
                                            name="{{ $field->name }}" 
                                            value="{{ old($field->name, $field->default_value) }}"
                                        >
                                        @break
                                        
                                    @default
                                        <input 
                                            type="{{ $field->type }}" 
                                            name="{{ $field->name }}" 
                                            class="form-control @error($field->name) is-invalid @enderror"
                                            placeholder="{{ $field->placeholder }}"
                                            value="{{ old($field->name, $field->default_value) }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                        >
                                @endswitch
                                
                                @if($field->help_text)
                                <div class="form-text">{{ $field->help_text }}</div>
                                @endif
                                
                                @error($field->name)
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                {{ $form->submit_button_text ?: 'Submit' }}
                            </button>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <p class="text-muted">No fields have been added to this form yet.</p>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
