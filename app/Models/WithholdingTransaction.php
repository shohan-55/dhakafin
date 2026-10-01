<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithholdingTransaction extends Model
{
    protected $fillable = [
        'organization_id','kind','reference','transaction_date','counterparty_name',
        'counterparty_tin','base_amount_minor','rate_ppm',
        'withheld_amount_minor','challan_reference','challan_date',
        'status','metadata','created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date'=>'date',
            'challan_date'=>'date',
            'metadata'=>'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
