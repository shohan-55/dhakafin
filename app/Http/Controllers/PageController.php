<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function home()
    {
        return view('home', [
            'services' => config('dhakafin.services'),
            'stats' => config('dhakafin.stats'),
            'packages' => config('dhakafin.packages'),
            'process' => config('dhakafin.process'),
            'why' => config('dhakafin.why'),
            'testimonials' => config('dhakafin.testimonials'),
            'meta' => [
                'title' => 'DhakaFin — Accounting, Audit, Tax, VAT & Advisory in Dhaka',
                'description' => 'Professional accounting, audit, tax, VAT and cost-efficiency services for individuals, startups and SMEs in Bangladesh. Compliant. Leak-free. In control.',
            ],
        ]);
    }

    public function about()
    {
        return view('about', [
            'services' => config('dhakafin.services'),
            'meta' => [
                'title' => 'About DhakaFin — One partner for compliance & cost control',
                'description' => 'DhakaFin is a finance, compliance and cost-control practice built for Bangladeshi SMEs.',
            ],
        ]);
    }

    public function pricing()
    {
        return view('pricing', [
            'packages' => config('dhakafin.packages'),
            'services' => config('dhakafin.services'),
            'meta' => [
                'title' => 'Pricing — DhakaFin',
                'description' => 'Transparent published pricing for accounting, tax, VAT, audit support and cost-efficiency services in Dhaka.',
            ],
        ]);
    }

    public function contact()
    {
        return view('contact', [
            'services' => config('dhakafin.services'),
            'meta' => [
                'title' => 'Contact DhakaFin — Book a free 15-minute consult',
                'description' => 'Talk to DhakaFin about accounting, tax, VAT, audit support or a CostGuard leak review.',
            ],
        ]);
    }

    public function service(string $slug)
    {
        $services = config('dhakafin.services');
        abort_unless(isset($services[$slug]), 404);

        return view('service', [
            'slug' => $slug,
            'service' => $services[$slug],
            'services' => $services,
            'meta' => [
                'title' => $services[$slug]['title'] . ' — DhakaFin',
                'description' => $services[$slug]['short'],
            ],
        ]);
    }
}
