<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    protected $fillable=[
        'organization_id','number','journal_date','source','reference','description',
        'status','created_by','posted_by','posted_at','integrity_hash','metadata',
    ];

    protected function casts(): array
    {
        return ['journal_date'=>'date','posted_at'=>'datetime','metadata'=>'array'];
    }

    protected static function booted(): void
    {
        static::updating(function (Journal $journal): void {
            if ($journal->getOriginal('status')==='posted') {
                throw new DomainException('Posted journals are immutable.');
            }
        });

        static::deleting(function (Journal $journal): void {
            if ($journal->status==='posted') {
                throw new DomainException('Posted journals cannot be deleted.');
            }
        });
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function lines(): HasMany { return $this->hasMany(JournalLine::class); }

    public function debitTotalMinor(): int { return (int) $this->lines()->sum('debit_minor'); }
    public function creditTotalMinor(): int { return (int) $this->lines()->sum('credit_minor'); }
}
