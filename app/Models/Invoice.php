<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable=[
        'organization_id','number','issue_date','due_date','status','currency',
        'subtotal_minor','tax_minor','total_minor','paid_minor','balance_minor','notes','metadata',
    ];

    protected function casts(): array
    {
        return ['issue_date'=>'date','due_date'=>'date','metadata'=>'array'];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function lines(): HasMany { return $this->hasMany(InvoiceLine::class); }
    public function allocations(): HasMany { return $this->hasMany(PaymentAllocation::class); }
}
