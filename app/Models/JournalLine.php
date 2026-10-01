<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    protected $fillable=['journal_id','account_id','description','debit_minor','credit_minor','metadata'];
    protected function casts(): array { return ['metadata'=>'array']; }

    protected static function booted(): void
    {
        static::updating(function (JournalLine $line): void {
            if ($line->journal()->where('status','posted')->exists()) {
                throw new DomainException('Lines of a posted journal are immutable.');
            }
        });

        static::deleting(function (JournalLine $line): void {
            if ($line->journal()->where('status','posted')->exists()) {
                throw new DomainException('Lines of a posted journal cannot be deleted.');
            }
        });
    }

    public function journal(): BelongsTo { return $this->belongsTo(Journal::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
}
