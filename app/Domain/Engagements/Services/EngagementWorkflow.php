<?php

namespace App\Domain\Engagements\Services;

use App\Domain\Engagements\EngagementStage;
use App\Models\Engagement;
use DomainException;

final class EngagementWorkflow
{
    private const NEXT = [
        'requested' => 'scoped',
        'scoped' => 'accepted',
        'accepted' => 'information_requested',
        'information_requested' => 'information_received',
        'information_received' => 'preparation',
        'preparation' => 'review',
        'review' => 'client_query',
        'client_query' => 'finalization',
        'finalization' => 'delivered',
        'delivered' => 'invoiced',
        'invoiced' => 'closed',
    ];

    public function advance(Engagement $engagement): Engagement
    {
        $next = self::NEXT[$engagement->stage] ?? null;

        if (!$next) {
            throw new DomainException('Engagement cannot advance beyond its current stage.');
        }

        $engagement->forceFill([
            'stage' => $next,
            'status' => $next === EngagementStage::Closed->value ? 'closed' : 'active',
        ])->save();

        return $engagement->refresh();
    }
}
