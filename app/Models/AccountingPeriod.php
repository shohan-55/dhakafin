<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPeriod extends Model
{
    protected $fillable=['organization_id','starts_on','ends_on','status','closed_by','closed_at'];
    protected function casts(): array { return ['starts_on'=>'date','ends_on'=>'date','closed_at'=>'datetime']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}
