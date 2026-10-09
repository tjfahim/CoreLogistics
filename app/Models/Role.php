<?php
// app/Models/Role.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'status'];
    protected $casts    = ['status' => 'boolean'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($role) {
            if (empty($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });

        static::updating(function ($role) {
            if ($role->isDirty('name') && !$role->isDirty('slug')) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions()->where('slug', $slug)->exists();
    }

    public function syncPermissions(array $ids): void
    {
        $this->permissions()->sync($ids);
    }

    /**
     * Permissions grouped by group_name.
     */
    public function permissionsGrouped()
    {
        return $this->permissions()
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}