@extends('themes.general.layouts.app')

@section('title', $form->title ?: $form->name)

@section('meta')
    <meta name="description" content="{{ Str::limit(strip_tags($form->description ?? ''), 160) }}">
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-2">{{ $form->title ?: $form->name }}</h1>

                    @if($form->description)
                        <div class="text-muted mb-4">{!! nl2br(e($form->description)) !!}</div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <strong>Please correct the highlighted fields.</strong>
                        </div>
                    @endif

                    {{-- The public form had no view at all: every active form opened a 500. --}}
                    <form method="POST" action="{{ route('forms.submit', $form->slug) }}" enctype="multipart/form-data" novalidate>
                        @csrf

                        <div class="row g-3">
                            @foreach($form->fields->where('is_visible', 1)->sortBy('order') as $field)
                                @php
                                    $name = $field->name;
                                    $id = 'field_' . $name;
                                    $options = $field->options_array;
                                    $old = old($name);
                                    $col = in_array($field->type, ['hidden']) ? 12 : (int) ($field->width ?: 12);
                                @endphp

                                @if($field->type === 'hidden')
                                    <input type="hidden" name="{{ $name }}" value="{{ $old ?? $field->default_value }}">
                                @else
                                    <div class="col-md-{{ $col }}">
                                        <label for="{{ $id }}" class="form-label">
                                            {{ $field->label }}
                                            @if($field->is_required)<span class="text-danger">*</span>@endif
                                        </label>

                                        @switch($field->type)
                                            @case('textarea')
                                                <textarea name="{{ $name }}" id="{{ $id }}" rows="4"
                                                          class="form-control @error($name) is-invalid @enderror"
                                                          placeholder="{{ $field->placeholder }}">{{ $old ?? $field->default_value }}</textarea>
                                                @break

                                            @case('select')
                                                <select name="{{ $name }}" id="{{ $id }}"
                                                        class="form-select @error($name) is-invalid @enderror">
                                                    <option value="">{{ $field->placeholder ?: 'Please select…' }}</option>
                                                    @foreach($options as $option)
                                                        <option value="{{ $option }}" @selected($old === (string) $option)>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                                @break

                                            @case('radio')
                                                <div class="@error($name) is-invalid @enderror">
                                                    @foreach($options as $i => $option)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="{{ $name }}"
                                                                   id="{{ $id }}_{{ $i }}" value="{{ $option }}" @checked($old === (string) $option)>
                                                            <label class="form-check-label" for="{{ $id }}_{{ $i }}">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @break

                                            @case('checkbox')
                                                <div class="@error($name) is-invalid @enderror">
                                                    @foreach($options as $i => $option)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="{{ $name }}[]"
                                                                   id="{{ $id }}_{{ $i }}" value="{{ $option }}"
                                                                   @checked(is_array($old) && in_array((string) $option, $old, true))>
                                                            <label class="form-check-label" for="{{ $id }}_{{ $i }}">{{ $option }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @break

                                            @case('file')
                                                <input type="file" name="{{ $name }}" id="{{ $id }}"
                                                       class="form-control @error($name) is-invalid @enderror">
                                                @break

                                            @case('range')
                                                <input type="range" name="{{ $name }}" id="{{ $id }}"
                                                       class="form-range @error($name) is-invalid @enderror"
                                                       value="{{ $old ?? $field->default_value }}">
                                                @break

                                            @case('color')
                                                <input type="color" name="{{ $name }}" id="{{ $id }}"
                                                       class="form-control form-control-color @error($name) is-invalid @enderror"
                                                       value="{{ $old ?? ($field->default_value ?: '#000000') }}">
                                                @break

                                            @default
                                                @php
                                                    $inputType = [
                                                        'phone' => 'tel', 'tel' => 'tel', 'datetime' => 'datetime-local',
                                                    ][$field->type] ?? $field->type;
                                                    $inputType = in_array($inputType, ['text','email','number','date','time','datetime-local','password','url','tel'], true) ? $inputType : 'text';
                                                @endphp
                                                <input type="{{ $inputType }}" name="{{ $name }}" id="{{ $id }}"
                                                       class="form-control @error($name) is-invalid @enderror"
                                                       placeholder="{{ $field->placeholder }}"
                                                       value="{{ $old ?? $field->default_value }}">
                                        @endswitch

                                        @if($field->help_text)
                                            <div class="form-text">{{ $field->help_text }}</div>
                                        @endif

                                        @error($name)
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary mt-4">
                            {{ $form->submit_button_text ?: 'Submit' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
