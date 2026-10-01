<?php

namespace App\Models;

use App\Domain\Engagements\EngagementStage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Engagement extends Model
{
    protected $fillable = [
        'organization_id','code','service_type','title','status','stage',
        'start_date','due_date','lead_user_id','client_contact_user_id','metadata',
    ];

    protected function casts(): array
    {
        return [
            'stage'=>EngagementStage::class,
            'start_date'=>'date',
            'due_date'=>'date',
            'metadata'=>'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
