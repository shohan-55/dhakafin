<?php

namespace App\Http\Controllers\Tools;

use App\Domain\TaxVat\Services\BdtMoney;
use App\Domain\TaxVat\Services\WithholdingCalculator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class WithholdingCalculatorController extends Controller
{
    public function create(): View
    {
        return view('tools.withholding-calculator', ['result' => null]);
    }

    public function store(
        Request $request,
        BdtMoney $money,
        WithholdingCalculator $calculator
    ): View {
        $validated = $request->validate([
            'kind' => ['required','in:tds,vds'],
            'amount' => ['required','regex:/^\s*[\d,]+(?:\.\d{1,2})?\s*$/'],
            'rate' => ['required','regex:/^\d+(?:\.\d{1,4})?$/'],
        ]);

        try {
            $baseMinor = $money->parseToMinor($validated['amount']);
            $calculation = $calculator->calculate($baseMinor, $validated['rate']);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'amount' => $exception->getMessage(),
            ]);
        }

        $result = [
            'kind' => strtoupper($validated['kind']),
            'base' => $money->formatMinor($calculation['base_amount_minor']),
            'rate' => rtrim(rtrim($calculation['rate_percent'], '0'), '.'),
            'withheld' => $money->formatMinor($calculation['withheld_amount_minor']),
            'net' => $money->formatMinor($calculation['net_amount_minor']),
        ];

        return view('tools.withholding-calculator', compact('result'));
    }
}
