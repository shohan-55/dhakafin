<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','password'];

    protected $hidden = [
        'password','remember_token','mfa_secret','mfa_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'=>'datetime',
            'password'=>'hashed',
            'mfa_secret'=>'encrypted',
            'mfa_recovery_codes'=>'encrypted:array',
            'mfa_enabled_at'=>'datetime',
        ];
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot(['role','status'])
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'organization_role_user')
            ->withPivot('organization_id')
            ->withTimestamps();
    }

    public function hasPermission(string $permission, Organization $organization): bool
    {
        return $this->roles()
            ->wherePivot('organization_id', $organization->getKey())
            ->whereHas('permissions', fn ($query) => $query->where('permissions.slug', $permission))
            ->exists();
    }
}
