<?php

namespace App\Domain\Engagements;

enum EngagementStage: string
{
    case Requested = 'requested';
    case Scoped = 'scoped';
    case Accepted = 'accepted';
    case InformationRequested = 'information_requested';
    case InformationReceived = 'information_received';
    case Preparation = 'preparation';
    case Review = 'review';
    case ClientQuery = 'client_query';
    case Finalization = 'finalization';
    case Delivered = 'delivered';
    case Invoiced = 'invoiced';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::InformationRequested => 'Information Requested',
            self::InformationReceived => 'Information Received',
            self::ClientQuery => 'Client Query',
            default => str($this->value)->replace('_', ' ')->title()->toString(),
        };
    }
}
