<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'user_type',
        'user_id',
        'guest_email',
        'ip_address',
        'user_agent',
        'data',
        'notes',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the form
     */
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    /**
     * Get the user (if logged in)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get data as array
     */
    public function getDataArrayAttribute()
    {
        if (empty($this->data)) {
            return [];
        }
        
        return is_array($this->data) ? $this->data : json_decode($this->data, true) ?? [];
    }

    /**
     * Set data from array
     */
    public function setDataArrayAttribute($value)
    {
        $this->data = json_encode($value);
    }

    /**
     * Get a specific field value
     */
    public function getFieldValue($fieldName)
    {
        $data = $this->data_array;
        return $data[$fieldName] ?? null;
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Scope for unread submissions
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for guest submissions
     */
    public function scopeGuest($query)
    {
        return $query->where('user_type', 'guest');
    }

    /**
     * Scope for user submissions
     */
    public function scopeUsers($query)
    {
        return $query->where('user_type', 'user');
    }

    /**
     * Get user display name
     */
    public function getUserDisplayNameAttribute()
    {
        if ($this->user_type === 'user' && $this->user) {
            return $this->user->name;
        }
        
        return $this->guest_email ?? 'Guest';
    }

    /**
     * Check if submission is from a registered user
     */
    public function getIsFromUserAttribute()
    {
        return $this->user_type === 'user' && $this->user_id !== null;
    }

    /**
     * Get formatted submission data for display
     */
    public function getFormattedDataAttribute()
    {
        $form = $this->form;
        if (!$form) {
            return $this->data_array;
        }

        $formatted = [];
        $data = $this->data_array;
        
        foreach ($form->fields as $field) {
            $value = $data[$field->name] ?? null;
            
            // Format file uploads
            if ($field->type === 'file' && $value) {
                $formatted[$field->label] = '<a href="' . asset('storage/' . $value) . '" target="_blank">View File</a>';
            }
            // Format arrays (checkboxes)
            elseif (is_array($value)) {
                $formatted[$field->label] = implode(', ', $value);
            }
            // Format booleans
            elseif (is_bool($value)) {
                $formatted[$field->label] = $value ? 'Yes' : 'No';
            }
            else {
                $formatted[$field->label] = $value ?? '-';
            }
        }

        return $formatted;
    }
}
