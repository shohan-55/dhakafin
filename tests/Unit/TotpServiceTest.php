<?php

namespace Tests\Unit;

use App\Domain\Identity\Services\TotpService;
use PHPUnit\Framework\TestCase;

class TotpServiceTest extends TestCase
{
    public function test_hotp_core_matches_rfc_4226_known_vector(): void
    {
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

        $this->assertSame('755224', (new TotpService())->codeAtCounter($secret, 0));
    }

    public function test_invalid_code_shape_is_rejected(): void
    {
        $service = new TotpService();
        $this->assertFalse($service->verify($service->generateSecret(), '12ab'));
    }
}
