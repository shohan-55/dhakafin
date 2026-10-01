<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable=['organization_id','plan_code','status','starts_on','ends_on','entitlements','metadata'];
    protected function casts(): array { return ['starts_on'=>'date','ends_on'=>'date','entitlements'=>'array','metadata'=>'array']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}
