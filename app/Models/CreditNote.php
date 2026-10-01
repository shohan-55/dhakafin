<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    protected $fillable=['organization_id','invoice_id','number','issue_date','amount_minor','status','reason','metadata'];
    protected function casts(): array { return ['issue_date'=>'date','metadata'=>'array']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
