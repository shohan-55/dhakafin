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

    public function totalFormatted(): string { return $this->formatMinor($this->total_minor); }
    public function paidFormatted(): string { return $this->formatMinor($this->paid_minor); }
    public function balanceFormatted(): string { return $this->formatMinor($this->balance_minor); }

    private function formatMinor(int $minor): string
    {
        return number_format(intdiv($minor,100)).'.'.str_pad((string)($minor%100),2,'0',STR_PAD_LEFT);
    }
}
