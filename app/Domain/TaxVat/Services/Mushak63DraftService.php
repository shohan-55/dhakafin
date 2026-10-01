<?php

namespace App\Domain\TaxVat\Services;

use InvalidArgumentException;

final class Mushak63DraftService
{
    public function __construct(private readonly BdtMoney $money) {}

    public function build(array $rows): array
    {
        $lines = [];
        $totalValueMinor = 0;
        $totalVatMinor = 0;

        foreach ($rows as $index => $row) {
            $description = trim((string) ($row['description'] ?? ''));
            $quantity = trim((string) ($row['quantity'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            $vat = trim((string) ($row['vat'] ?? ''));

            if ($description === '' && $quantity === '' && $value === '' && $vat === '') {
                continue;
            }

            if ($description === '') {
                throw new InvalidArgumentException('Every Mushak line must have a description.');
            }

            if ($quantity === '' || !preg_match('/^\d+(?:\.\d{1,3})?$/', $quantity)) {
                throw new InvalidArgumentException('Quantity must be a non-negative number with up to three decimal places.');
            }

            $valueMinor = $this->money->parseToMinor($value);
            $vatMinor = $this->money->parseToMinor($vat);

            $lines[] = [
                'line_no' => count($lines) + 1,
                'description' => $description,
                'quantity' => $quantity,
                'value_minor' => $valueMinor,
                'vat_minor' => $vatMinor,
            ];

            $totalValueMinor += $valueMinor;
            $totalVatMinor += $vatMinor;
        }

        if ($lines === []) {
            throw new InvalidArgumentException('At least one Mushak line is required.');
        }

        return [
            'lines' => $lines,
            'total_value_minor' => $totalValueMinor,
            'total_vat_minor' => $totalVatMinor,
        ];
    }
}
