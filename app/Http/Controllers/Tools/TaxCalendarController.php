<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\ComplianceRule;
use Illuminate\Contracts\View\View;

class TaxCalendarController extends Controller
{
    public function __invoke(): View
    {
        $rules = ComplianceRule::query()
            ->published()
            ->orderBy('category')
            ->orderBy('title')
            ->get();

        return view('tools.tax-calendar', compact('rules'));
    }
}
