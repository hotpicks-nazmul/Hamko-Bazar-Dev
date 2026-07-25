<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
        'status',
        'provider',
        'provider_id',
        // Seller specific fields
        'shop_name',
        'shop_description',
        'shop_logo',
        'shop_banner',
        'business_registration_number',
        'tax_id',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_routing_code',
        'commission_rate',
        'wallet_balance',
        'wallet_points',
        'pending_balance',
        'verification_status',
        'verified_at',
        'verification_notes',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'return_address',
        'seller_type',
        'company_name',
        'company_address',
        // Staff specific fields
        'designation',
        'legacy_permissions',
        'warehouse_id',
        'is_super_admin',
        // Customer specific fields
        'loyalty_points',
        'loyalty_points_spent',
        'total_spent',
        'customer_group_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Check if user has a specific system role (column-based, NOT Spatie roles).
     * Renamed to avoid conflict with Spatie's HasRoles::hasRole().
     */
    public function hasSystemRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user has admin access (any level)
     */
    public function hasAdminAccess(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'staff']);
    }

    /**
     * Check if user has any of the given system roles (column-based).
     */
    public function hasAnySystemRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user is vendor
     */
    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Get user's orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get user's cart
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get user's wishlist
     */
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get user's reviews
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get user's addresses
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get user's default address
     */
    public function defaultAddress()
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    /**
     * Get user's chats
     */
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * Get products created by this user
     */
    public function createdProducts()
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    /**
     * Get products where this user is the seller
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * Get the notifications for this user.
     */
    public function notifications()
    {
        return $this->morphMany(\App\Models\Notification::class, 'notifiable');
    }

    /**
     * Get the customer group this user belongs to
     */
    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    /**
     * Get the customer segments this user belongs to
     */
    public function customerSegments()
    {
        return $this->belongsToMany(CustomerSegment::class, 'customer_segment_members', 'user_id', 'segment_id')
            ->withPivot('added_at')
            ->withTimestamps();
    }

    // ==================== Seller Methods ====================

    /**
     * Check if user is a seller (vendor)
     */
    public function isSeller(): bool
    {
        return $this->role === 'vendor';
    }

    /**
     * Get the seller's shop name
     */
    public function getShopNameAttribute(): string
    {
        return $this->shop_name ?? $this->name;
    }

    /**
     * Get shop logo URL
     */
    public function getShopLogoUrlAttribute(): ?string
    {
        return $this->shop_logo ? asset('uploads/shop_logos/' . $this->shop_logo) : null;
    }

    /**
     * Get shop banner URL
     */
    public function getShopBannerUrlAttribute(): ?string
    {
        return $this->shop_banner ? asset('uploads/shop_banners/' . $this->shop_banner) : null;
    }

    /**
     * Check if seller is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if seller verification is pending
     */
    public function isVerificationPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    /**
     * Check if seller verification is rejected
     */
    public function isVerificationRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    /**
     * Get seller status (active/inactive)
     */
    public function isActiveSeller(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get total products count
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Get total orders count
     */
    public function getOrdersCountAttribute(): int
    {
        return Order::whereHas('items', function ($query) {
            $query->whereHas('product', function ($q) {
                $q->where('seller_id', $this->id);
            });
        })->count();
    }

    /**
     * Get total sales amount
     */
    public function getTotalSalesAttribute(): float
    {
        return Order::whereHas('items', function ($query) {
            $query->whereHas('product', function ($q) {
                $q->where('seller_id', $this->id);
            });
        })->where('payment_status', 'paid')
          ->whereIn('status', ['delivered', 'shipped', 'confirmed'])
          ->sum('grand_total');
    }

    /**
     * Scope to get only sellers
     */
    public function scopeSellers($query)
    {
        return $query->where('role', 'vendor');
    }

    /**
     * Scope to get verified sellers
     */
    public function scopeVerifiedSellers($query)
    {
        return $query->where('role', 'vendor')->where('verification_status', 'verified');
    }

    /**
     * Scope to get only staff members
     */
    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }

    /**
     * Scope to get only admin users (not super_admin)
     */
    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope to get only super admin users
     */
    public function scopeSuperAdmin($query)
    {
        return $query->where('role', 'super_admin');
    }

    /**
     * Scope to get admin panel users (super_admin, admin, staff)
     */
    public function scopeAdminPanel($query)
    {
        return $query->whereIn('role', ['super_admin', 'admin', 'staff']);
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Get the warehouse associated with this staff member
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Scope to get sellers by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('role', 'vendor')->where('status', $status);
    }

    /**
     * Scope to get sellers by verification status
     */
    public function scopeByVerificationStatus($query, $verificationStatus)
    {
        return $query->where('role', 'vendor')->where('verification_status', $verificationStatus);
    }

    /**
     * Get the legacy permissions JSON column as an array.
     */
    public function getLegacyPermissionsAttribute(): array
    {
        $raw = $this->attributes['legacy_permissions'] ?? null;
        if (empty($raw)) return [];
        if (is_array($raw)) return $raw;
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Set the legacy permissions column from an array.
     */
    public function setLegacyPermissionsAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['legacy_permissions'] = json_encode($value);
        } else {
            $this->attributes['legacy_permissions'] = $value;
        }
    }

    /**
     * Get the Spatie permissions as an accessor for the legacy column.
     * Compatibility layer - reads Spatie permissions and returns them.
     */
    public function getPermissionsAttribute()
    {
        // Return Spatie permissions when accessed as property
        return $this->getRelationValue('permissions');
    }

    /**
     * Check if user has a specific permission (backward-compatible wrapper).
     * Uses Spatie's permission system, with fallback to legacy JSON column.
     * 
     * @param string $permission  Module key (e.g. 'products') or granular (e.g. 'products.view')
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->role === 'super_admin' || $this->is_super_admin) {
            return true;
        }

        // Check globally disabled permissions (toggled OFF in Permission Settings)
        // This check runs before all other logic so disabled keys are blocked
        // for all roles, while preserving Spatie pivot table entries.
        try {
            $disabledKeys = json_decode(\App\Models\Setting::get('disabled_permission_keys', '[]'), true) ?: [];
            if (in_array($permission, $disabledKeys)) {
                return false;
            }
        } catch (\Exception $e) {
            // If settings table is unavailable, fall through
        }

        // Admin role - full access by default, but restrictable via Staff Permissions modal
        if ($this->role === 'admin') {
            // Always allow dashboard access for admins
            if ($permission === 'dashboard') {
                return true;
            }

            if (!$this->hasExplicitPermissions()) {
                // Full access when no restrictions assigned
                // But for specific action permissions (e.g. 'products.edit'),
                // respect global toggle in Permission Settings: if the permission
                // key was deleted from Spatie via toggleKey(), don't grant it
                if (str_contains($permission, '.')) {
                    try {
                        if (!\Spatie\Permission\Models\Permission::where('name', $permission)
                            ->where('guard_name', 'web')
                            ->exists()) {
                            return false; // Key deleted from settings — deny
                        }
                    } catch (\Exception $e) {
                        // On DB error, fall back to full access
                    }
                }
                return true; // Full access when no restrictions assigned
            }
            // Fall through to the permission check below if explicitly managed
        }

        // Staff (or admin with explicit permissions) - check Spatie/granular
        if (in_array($this->role, ['admin', 'staff'])) {
            // Handle submenu permissions
            if (str_starts_with($permission, 'submenu:')) {
                $routeName = substr($permission, 8); // remove 'submenu:' prefix
                $disabledKey = 'submenu_disabled:' . $routeName;
                $enabledKey = 'submenu:' . $routeName;
                $legacyPerms = $this->getLegacyPermissionsAttribute();
                $rawLegacy = $this->attributes['legacy_permissions'] ?? null;
                // ON by default unless explicitly disabled
                if (empty($legacyPerms)) {
                    // null raw = never managed → show all
                    // non-null raw (e.g. '[]') = explicitly managed but cleared → hide
                    if ($rawLegacy === null) {
                        return true;
                    }
                    return false;
                }
                // Check if explicitly disabled
                if (in_array($disabledKey, $legacyPerms)) {
                    return false;
                }
                // Otherwise ON
                return true;
            }

            // Handle submenu_disabled: permission check
            if (str_starts_with($permission, 'submenu_disabled:')) {
                return false;
            }

            // Try exact Spatie permission match
            try {
                if ($this->hasPermissionTo($permission)) {
                    return true;
                }
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                // Permission doesn't exist in Spatie tables, fall through
            }

            // For bare section permissions (e.g. 'view-revenue'), also check if user
            // has any {module}.{permission} permission (e.g. 'dashboard.view-revenue')
            try {
                $suffixCheck = '.' . $permission;
                foreach ($this->getAllPermissions() as $perm) {
                    if (str_ends_with($perm->name, $suffixCheck)) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                // Ignore relation loading errors
            }

            // If it's a module-level key (e.g. 'products'), also check if user
            // has any granular permission under that module (e.g. 'products.view')
            if (!str_contains($permission, '.')) {
                try {
                    $granularChecker = $permission . '.';
                    foreach ($this->getAllPermissions() as $perm) {
                        if (str_starts_with($perm->name, $granularChecker)) {
                            return true;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore relation loading errors
                }
            }

            // Fallback to legacy permissions column
            $legacyPerms = $this->getLegacyPermissionsAttribute();
            if (!empty($legacyPerms) && in_array($permission, $legacyPerms)) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Check if admin user has explicitly assigned permissions via Staff Permissions modal.
     * If false, the admin gets full access by default.
     */
    private function hasExplicitPermissions(): bool
    {
        // Check Spatie permissions
        if ($this->relationLoaded('permissions')) {
            if ($this->permissions->isNotEmpty()) {
                return true;
            }
        } else {
            if ($this->permissions()->exists()) {
                return true;
            }
        }

        // Check legacy permissions column
        $legacy = $this->getLegacyPermissionsAttribute();
        if (!empty($legacy)) {
            return true;
        }

        return false;
    }

    /**
     * Get user's permissions as array (backward compatible).
     * Returns legacy module keys that the user has.
     * 
     * @return array
     */
    public function getPermissionsArray(): array
    {
        $keys = \App\Helpers\PermissionHelper::legacyKeys();
        $assigned = [];

        foreach ($keys as $key) {
            if ($this->hasPermission($key)) {
                $assigned[] = $key;
            }
        }

        return $assigned;
    }

    /**
     * Get the first allowed route based on user permissions.
     * 
     * @return string
     */
    public function getFirstAllowedRoute(): string
    {
        if ($this->role === 'super_admin' || $this->is_super_admin) {
            return 'admin.dashboard';
        }

        $legacyKeys = \App\Helpers\PermissionHelper::legacyKeys();

        if ($this->hasPermission('dashboard')) {
            return 'admin.dashboard';
        }

        foreach ($legacyKeys as $key) {
            if ($key === 'dashboard') continue;
            if ($this->hasPermission($key)) {
                $route = \App\Helpers\PermissionHelper::permissionToRoute($key);
                if ($route) return $route;
            }
        }

        return 'admin.dashboard';
    }
}
