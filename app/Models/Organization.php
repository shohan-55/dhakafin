<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = ['name','slug','legal_name','tin','bin','entity_type','status','timezone'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role','status'])
            ->withTimestamps();
    }

    public function complianceObligations(): HasMany
    {
        return $this->hasMany(ComplianceObligation::class);
    }
}
