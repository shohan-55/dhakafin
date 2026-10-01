<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = [
        'organization_id','engagement_id','uploaded_by','title','original_name',
        'disk','path','mime_type','size_bytes','checksum_sha256','status',
        'sensitivity','review_status','reviewed_by','reviewed_at','metadata',
    ];

    protected $hidden = ['path'];

    protected function casts(): array
    {
        return ['reviewed_at'=>'datetime','metadata'=>'array'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(DocumentReview::class);
    }
}
