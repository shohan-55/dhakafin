<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceRule extends Model
{
    protected $fillable = [
        'jurisdiction','code','category','title','frequency','rule_data',
        'effective_from','effective_to','source_url','verified_at',
        'published_at','is_active',
    ];

    protected function casts(): array
    {
        return [
            'rule_data'=>'array',
            'effective_from'=>'date',
            'effective_to'=>'date',
            'verified_at'=>'datetime',
            'published_at'=>'datetime',
            'is_active'=>'boolean',
        ];
    }

    public function obligations(): HasMany
    {
        return $this->hasMany(ComplianceObligation::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_active', true)
            ->whereNotNull('source_url')
            ->whereNotNull('published_at')
            ->whereNotNull('verified_at');
    }
}
