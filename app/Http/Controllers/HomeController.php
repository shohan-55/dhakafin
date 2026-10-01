<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'services' => [
                'Accounting & Bookkeeping',
                'Statutory Audit Support',
                'Corporate & Personal Tax',
                'VAT & VDS Compliance',
                'Virtual CFO',
                'Cost & Internal Control Review',
            ],
        ]);
    }
}
