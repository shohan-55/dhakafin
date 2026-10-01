<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MushakForm extends Model
{
    protected $fillable = [
        'organization_id','form_type','serial_no','issue_date','buyer_name',
        'buyer_bin','buyer_address','lines','total_value_minor',
        'total_vat_minor','status','finalized_at','created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'=>'date',
            'lines'=>'array',
            'finalized_at'=>'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
