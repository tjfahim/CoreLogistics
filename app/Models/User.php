<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',      // legacy flag: 1=user, 2=admin
        'role_id',   // FK to roles
        'status',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getImageUrlAttribute()
    {
        if ($this->image && file_exists(public_path('uploads/users/' . $this->image))) {
            return asset('uploads/users/' . $this->image);
        }
        return asset('admin/images/admin.jpg');
    }

    public function isAdmin(): bool
    {
        return (int) $this->role === 2;
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->roleRelation) {
            return false;
        }

        return $this->roleRelation->hasPermission($slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->roleRelation) {
            return false;
        }

        return $this->roleRelation->permissions()
            ->whereIn('slug', $slugs)
            ->exists();
    }

    public function hasAllPermissions(array $slugs): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!$this->roleRelation) {
            return false;
        }

        $count = $this->roleRelation->permissions()
            ->whereIn('slug', $slugs)
            ->count();

        return $count === count($slugs);
    }
}