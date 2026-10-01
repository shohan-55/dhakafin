<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalLine extends Model
{
    protected $fillable=['journal_id','account_id','description','debit_minor','credit_minor','metadata'];
    protected function casts(): array { return ['metadata'=>'array']; }
    public function journal(): BelongsTo { return $this->belongsTo(Journal::class); }
    public function account(): BelongsTo { return $this->belongsTo(Account::class); }
}
