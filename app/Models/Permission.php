<?php
// app/Models/Permission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'group_name',
    ];

    /**
     * Auto-generate slug if not provided.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($permission) {
            if (empty($permission->slug)) {
                $permission->slug = Str::slug($permission->name);
            }
        });

        static::updating(function ($permission) {
            if ($permission->isDirty('name') && !$permission->isDirty('slug')) {
                $permission->slug = Str::slug($permission->name);
            }
        });
    }

    /**
     * Relationship: Permission belongs to many Roles.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Scope: filter by group.
     */
    public function scopeInGroup($query, string $groupName)
    {
        return $query->where('group_name', $groupName);
    }

    /**
     * Get all unique group names (for dropdown).
     */
    public static function getGroupNames()
    {
        return self::query()
            ->select('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');
    }

    /**
     * Group permissions by their group_name.
     */
    public static function grouped()
    {
        return self::orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');
    }
}