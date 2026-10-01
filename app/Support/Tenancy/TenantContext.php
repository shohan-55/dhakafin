<?php

namespace App\Support\Tenancy;

use App\Models\Organization;
use LogicException;

class TenantContext
{
    private ?Organization $organization = null;

    public function set(Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function clear(): void
    {
        $this->organization = null;
    }

    public function current(): Organization
    {
        return $this->organization ?? throw new LogicException('No organization is active in the tenant context.');
    }

    public function id(): int
    {
        return $this->current()->getKey();
    }

    public function hasCurrent(): bool
    {
        return $this->organization !== null;
    }
}
