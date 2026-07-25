<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKeyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_key_id',
        'method',
        'endpoint',
        'status_code',
        'response_time',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'response_time' => 'integer',
    ];

    /**
     * Get the API key that owns this log
     */
    public function apiKey()
    {
        return $this->belongsTo(ApiKey::class);
    }
}
