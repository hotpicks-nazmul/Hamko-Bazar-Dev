<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'label',
        'name',
        'type',
        'placeholder',
        'help_text',
        'is_required',
        'is_unique',
        'validation_rules',
        'options',
        'width',
        'order',
        'is_visible',
        'is_editable',
        'default_value',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_unique' => 'boolean',
        'is_visible' => 'boolean',
        'is_editable' => 'boolean',
        'width' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($field) {
            if (empty($field->name)) {
                $field->name = Str::slug($field->label, '_');
            }
            
            // Ensure unique name within form
            $originalName = $field->name;
            $counter = 1;
            while (static::where('form_id', $field->form_id)->where('name', $field->name)->exists()) {
                $field->name = $originalName . '_' . $counter;
                $counter++;
            }
        });
    }

    /**
     * Get the form
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    /**
     * Get options as array
     */
    public function getOptionsArrayAttribute()
    {
        if (empty($this->options)) {
            return [];
        }
        
        return is_array($this->options) ? $this->options : json_decode($this->options, true) ?? [];
    }

    /**
     * Set options from array
     */
    public function setOptionsArrayAttribute($value)
    {
        $this->options = json_encode($value);
    }

    /**
     * Get validation rules as array
     */
    public function getValidationRulesArrayAttribute()
    {
        if (empty($this->validation_rules)) {
            return [];
        }
        
        return is_array($this->validation_rules) ? $this->validation_rules : json_decode($this->validation_rules, true) ?? [];
    }

    /**
     * Get field type icon
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            'text' => 'bi-type',
            'textarea' => 'bi-textarea-resize',
            'email' => 'bi-envelope',
            'phone' => 'bi-telephone',
            'number' => 'bi-123',
            'select' => 'bi-caret-down-fill',
            'radio' => 'bi-circle',
            'checkbox' => 'bi-check-square',
            'date' => 'bi-calendar',
            'time' => 'bi-clock',
            'datetime' => 'bi-calendar-event',
            'file' => 'bi-file-earmark-arrow-up',
            'hidden' => 'bi-eye-slash',
            'password' => 'bi-key',
            'url' => 'bi-link',
            'color' => 'bi-palette',
            'range' => 'bi-sliders',
            'tel' => 'bi-telephone',
        ];

        return $icons[$this->type] ?? 'bi-input-cursor-text';
    }

    /**
     * Check if field type has options
     */
    public function hasOptions()
    {
        return in_array($this->type, ['select', 'radio', 'checkbox']);
    }

    /**
     * Check if field is input type
     */
    public function isInputType()
    {
        return in_array($this->type, ['text', 'email', 'phone', 'number', 'url', 'tel', 'password', 'color', 'range']);
    }

    /**
     * Check if field is file type
     */
    public function isFileType()
    {
        return $this->type === 'file';
    }

    /**
     * Build validation rules string
     */
    public function getValidationStringAttribute()
    {
        $rules = [];
        
        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        $validationRules = $this->validation_rules_array;
        
        if (!empty($validationRules)) {
            foreach ($validationRules as $rule) {
                if (is_string($rule)) {
                    $rules[] = $rule;
                } elseif (is_array($rule)) {
                    $ruleName = $rule['rule'] ?? null;
                    $ruleParams = $rule['params'] ?? [];
                    if ($ruleName) {
                        $rules[] = $ruleName . ':' . implode(',', $ruleParams);
                    }
                }
            }
        }

        return implode('|', $rules);
    }

    /**
     * Scope for visible fields
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
