<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    protected $fillable=[
        'invoice_id','description','quantity_milli','unit_amount_minor',
        'line_total_minor','tax_minor','metadata',
    ];

    protected function casts(): array { return ['metadata'=>'array']; }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
