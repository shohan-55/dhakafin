<?php

namespace App\Domain\TaxVat\Services;

use InvalidArgumentException;

final class BdtMoney
{
    public function parseToMinor(string $amount): int
    {
        $normalized = str_replace([',',' '], '', trim($amount));

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('Amount must be a positive BDT value with up to two decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '');
        $minor = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        return $minor;
    }

    public function formatMinor(int $minor): string
    {
        $sign = $minor < 0 ? '-' : '';
        $minor = abs($minor);

        return $sign.number_format(intdiv($minor, 100)).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
