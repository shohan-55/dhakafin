<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable=[
        'organization_id','idempotency_key','payment_date','amount_minor',
        'allocated_amount_minor','unapplied_amount_minor','method','reference',
        'status','received_by','metadata',
    ];

    protected function casts(): array { return ['payment_date'=>'date','metadata'=>'array']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function allocations(): HasMany { return $this->hasMany(PaymentAllocation::class); }
}
