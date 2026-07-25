<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'subject_id' => 'integer',
        'causer_id' => 'integer',
    ];

    /**
     * Log names
     */
    const LOG_ADMIN = 'admin';
    const LOG_CUSTOMER = 'customer';
    const LOG_SYSTEM = 'system';
    const LOG_API = 'api';

    /**
     * Get the user (causer) that performed the activity.
     */
    public function causer()
    {
        return $this->morphTo('causer', 'causer_type', 'causer_id');
    }

    /**
     * Get the subject of the activity.
     */
    public function subject()
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }

    /**
     * Scope to filter by log name.
     */
    public function scopeForLog($query, $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope to filter admin logs.
     */
    public function scopeAdmin($query)
    {
        return $query->where('log_name', self::LOG_ADMIN);
    }

    /**
     * Scope to filter customer logs.
     */
    public function scopeCustomer($query)
    {
        return $query->where('log_name', self::LOG_CUSTOMER);
    }

    /**
     * Scope to filter system logs.
     */
    public function scopeSystem($query)
    {
        return $query->where('log_name', self::LOG_SYSTEM);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('causer_id', $userId)
            ->where('causer_type', 'App\Models\User');
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for recent activities.
     */
    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Get the activity description with formatted properties.
     */
    public function getFormattedDescriptionAttribute()
    {
        $description = $this->description;
        
        if ($this->properties) {
            if (isset($this->properties['attributes'])) {
                $description .= ' - ' . json_encode($this->properties['attributes']);
            }
        }
        
        return $description;
    }

    /**
     * Static method to log an activity.
     */
    public static function log(
        string $logName,
        string $description,
        $subject = null,
        $causer = null,
        array $properties = []
    ): self {
        $activity = new self();
        $activity->log_name = $logName;
        $activity->description = $description;
        
        if ($subject) {
            $activity->subject_type = get_class($subject);
            $activity->subject_id = $subject->getKey();
        }
        
        if ($causer) {
            $activity->causer_type = get_class($causer);
            $activity->causer_id = $causer->getKey();
        }
        
        if (!empty($properties)) {
            $activity->properties = $properties;
        }
        
        // Get IP and User Agent
        $activity->ip_address = request()->ip();
        $activity->user_agent = request()->userAgent();
        
        $activity->save();
        
        return $activity;
    }

    /**
     * Log admin activity.
     */
    public static function adminLog(string $description, $subject = null, $causer = null, array $properties = []): self
    {
        return self::log(self::LOG_ADMIN, $description, $subject, $causer, $properties);
    }

    /**
     * Log customer activity.
     */
    public static function customerLog(string $description, $subject = null, $causer = null, array $properties = []): self
    {
        return self::log(self::LOG_CUSTOMER, $description, $subject, $causer, $properties);
    }

    /**
     * Log system activity.
     */
    public static function systemLog(string $description, $subject = null, array $properties = []): self
    {
        return self::log(self::LOG_SYSTEM, $description, $subject, null, $properties);
    }
}
