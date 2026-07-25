<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginCode extends Model
{
    protected $fillable = ['email', 'code', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function scopeValid($query)
    {
        return $query->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    public static function generateFor(string $email): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        static::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    public static function verify(string $email, string $code): bool
    {
        $record = static::where('email', $email)
            ->where('code', $code)
            ->valid()
            ->latest()
            ->first();

        if (!$record) {
            return false;
        }

        $record->update(['used_at' => now()]);
        return true;
    }
}
