<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable=['organization_id','payment_id','refund_date','amount_minor','reference','status','reason','metadata'];
    protected function casts(): array { return ['refund_date'=>'date','metadata'=>'array']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
