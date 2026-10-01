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

    public function baseAmountFormatted(): string
    {
        return $this->formatMinor($this->base_amount_minor);
    }

    public function withheldAmountFormatted(): string
    {
        return $this->formatMinor($this->withheld_amount_minor);
    }

    public function ratePercentFormatted(): string
    {
        $whole = intdiv($this->rate_ppm, 10000);
        $fraction = str_pad((string) ($this->rate_ppm % 10000), 4, '0', STR_PAD_LEFT);

        return rtrim(rtrim($whole.'.'.$fraction, '0'), '.');
    }

    private function formatMinor(int $minor): string
    {
        return number_format(intdiv($minor, 100)).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
