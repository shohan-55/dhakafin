<?php

namespace App\Domain\TaxVat\Services;

use InvalidArgumentException;

final class WithholdingCalculator
{
    public function calculate(int $baseAmountMinor, string $rate): array
    {
        if ($baseAmountMinor < 0) {
            throw new InvalidArgumentException('Base amount cannot be negative.');
        }

        if (!preg_match('/^\d+(?:\.\d{1,4})?$/', $rate)) {
            throw new InvalidArgumentException('Rate must be a non-negative percentage with up to four decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $rate, 2), 2, '');
        $basisPoints = ((int) $whole * 10000) + (int) str_pad(substr($fraction, 0, 4), 4, '0');

        if ($basisPoints > 1000000) {
            throw new InvalidArgumentException('Rate cannot exceed 100%.');
        }

        $withheldMinor = intdiv(($baseAmountMinor * $basisPoints) + 500000, 1000000);

        return [
            'base_amount_minor' => $baseAmountMinor,
            'rate_percent' => number_format($basisPoints / 10000, 4, '.', ''),
            'withheld_amount_minor' => $withheldMinor,
            'net_amount_minor' => $baseAmountMinor - $withheldMinor,
        ];
    }
}
