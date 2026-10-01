@extends('layouts.workspace')
@section('title','Billing — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.dashboard') }}">Workspace</a></div></nav>
<main class="wsmain">
<div style="display:flex;justify-content:space-between;gap:20px;align-items:end;flex-wrap:wrap"><div><div class="eyebrow">Billing</div><h2 style="margin-bottom:8px">Invoices & payments</h2><p class="muted">BDT amounts are stored in integer minor units with explicit allocation and unapplied balances.</p></div>
@if(auth()->user()->hasPermission('billing.manage',$organization))<a class="btn primary" href="{{ route('workspace.billing.invoices.create') }}">New invoice</a>@endif</div>
@if(session('status'))<div class="wscard" style="margin:20px 0">{{ session('status') }}</div>@endif

<div class="wscard" style="margin-top:24px;overflow:auto"><h3>Invoices</h3><table style="width:100%;border-collapse:collapse"><thead><tr style="text-align:left"><th style="padding:10px">Invoice</th><th style="padding:10px">Issue</th><th style="padding:10px">Total</th><th style="padding:10px">Paid</th><th style="padding:10px">Balance</th><th style="padding:10px">Status</th><th></th></tr></thead><tbody>
@forelse($invoices as $invoice)
<tr style="border-top:1px solid #e2eeeb"><td style="padding:10px">{{ $invoice->number }}</td><td style="padding:10px">{{ $invoice->issue_date->format('d M Y') }}</td><td style="padding:10px">৳ {{ $invoice->totalFormatted() }}</td><td style="padding:10px">৳ {{ $invoice->paidFormatted() }}</td><td style="padding:10px">৳ {{ $invoice->balanceFormatted() }}</td><td style="padding:10px">{{ str($invoice->status)->replace('_',' ')->title() }}</td><td style="padding:10px">@if($invoice->status==='draft' && auth()->user()->hasPermission('billing.manage',$organization))<form method="post" action="{{ route('workspace.billing.invoices.issue',$invoice) }}">@csrf @method('PATCH')<button class="btn ghost" style="min-height:36px" type="submit">Issue</button></form>@endif</td></tr>
@empty<tr><td colspan="7" class="muted" style="padding:16px">No invoice yet.</td></tr>@endforelse
</tbody></table></div>

@if(auth()->user()->hasPermission('billing.manage',$organization))
<div class="wscard" style="margin-top:16px"><h3>Record payment</h3><form method="post" action="{{ route('workspace.billing.payments.store') }}">@csrf
<input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px">
<div class="field"><label>Date</label><input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required></div>
<div class="field"><label>Amount (BDT)</label><input name="amount" required></div>
<div class="field"><label>Method</label><select name="method"><option value="bank">Bank</option><option value="cash">Cash</option><option value="bkash">bKash</option><option value="nagad">Nagad</option><option value="card">Card</option><option value="other">Other</option></select></div>
<div class="field"><label>Reference</label><input name="reference"></div>
<div class="field"><label>Allocate to invoice</label><select name="invoice_id"><option value="">Leave unapplied</option>@foreach($invoices->whereIn('status',['issued','partially_paid']) as $invoice)<option value="{{ $invoice->id }}">{{ $invoice->number }} · ৳ {{ $invoice->balanceFormatted() }}</option>@endforeach</select></div>
<div class="field"><label>Allocation amount (optional)</label><input name="allocation_amount"></div>
</div><button class="btn primary" style="border:0" type="submit">Record payment</button></form></div>
@endif

<div class="wscard" style="margin-top:16px;overflow:auto"><h3>Payments</h3><table style="width:100%;border-collapse:collapse"><thead><tr style="text-align:left"><th style="padding:10px">Date</th><th style="padding:10px">Reference</th><th style="padding:10px">Amount</th><th style="padding:10px">Allocated</th><th style="padding:10px">Unapplied</th></tr></thead><tbody>
@forelse($payments as $payment)<tr style="border-top:1px solid #e2eeeb"><td style="padding:10px">{{ $payment->payment_date->format('d M Y') }}</td><td style="padding:10px">{{ $payment->reference ?: '—' }}</td><td style="padding:10px">৳ {{ $payment->amountFormatted() }}</td><td style="padding:10px">৳ {{ $payment->allocatedFormatted() }}</td><td style="padding:10px">৳ {{ $payment->unappliedFormatted() }}</td></tr>@empty<tr><td colspan="5" class="muted" style="padding:16px">No payment yet.</td></tr>@endforelse
</tbody></table></div>
</main>
@endsection
