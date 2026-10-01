<?php

namespace App\Support\Auditing;

use App\Models\AuditEvent;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function record(
        string $action,
        ?User $actor = null,
        ?Organization $organization = null,
        ?Model $auditable = null,
        array $metadata = [],
        ?Request $request = null,
    ): AuditEvent {
        $event = new AuditEvent([
            'organization_id' => $organization?->getKey(),
            'actor_user_id' => $actor?->getKey(),
            'action' => $action,
            'metadata' => $metadata ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'occurred_at' => now(),
        ]);

        if ($auditable) {
            $event->auditable()->associate($auditable);
        }

        $event->save();

        return $event;
    }
}
