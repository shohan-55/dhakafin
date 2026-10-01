<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceObligation extends Model
{
    protected $fillable = [
        'organization_id','compliance_rule_id','type','title','period_key','due_date','status',
        'responsible_user_id','completed_at','evidence_reference','notes','metadata',
    ];

    protected function casts(): array
    {
        return [
            'due_date'=>'date',
            'completed_at'=>'datetime',
            'metadata'=>'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(ComplianceRule::class, 'compliance_rule_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
