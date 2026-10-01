<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable=[
        'organization_id','code','name','type','normal_balance','parent_id',
        'is_postable','is_active','metadata',
    ];

    protected function casts(): array
    {
        return ['is_postable'=>'boolean','is_active'=>'boolean','metadata'=>'array'];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function parent(): BelongsTo { return $this->belongsTo(Account::class,'parent_id'); }
    public function children(): HasMany { return $this->hasMany(Account::class,'parent_id'); }
    public function journalLines(): HasMany { return $this->hasMany(JournalLine::class); }
}
