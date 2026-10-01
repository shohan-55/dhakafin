@extends('layouts.workspace')
@section('title','Accounting — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.dashboard') }}">Workspace</a></div></nav>
<main class="wsmain">
<div style="display:flex;justify-content:space-between;gap:20px;align-items:end;flex-wrap:wrap">
<div><div class="eyebrow">Double-entry accounting</div><h2 style="margin-bottom:8px">Ledger & trial balance</h2><p class="muted">Posted journals are balanced, tenant-scoped and protected against mutation.</p></div>
<div style="display:flex;gap:10px;flex-wrap:wrap">
@if($accounts->isEmpty() && auth()->user()->hasPermission('accounting.manage',$organization))
<form method="post" action="{{ route('workspace.accounting.bootstrap') }}">@csrf<button class="btn ghost" type="submit">Create standard COA</button></form>
@endif
@if($accounts->isNotEmpty() && auth()->user()->hasPermission('accounting.manage',$organization))
<a class="btn primary" href="{{ route('workspace.accounting.journals.create') }}">New journal</a>
@endif
</div></div>
@if(session('status'))<div class="wscard" style="margin:20px 0">{{ session('status') }}</div>@endif
@error('journal')<div class="wscard error" style="margin:20px 0">{{ $message }}</div>@enderror

<div class="wscard" style="margin-top:24px;overflow:auto"><h3>Trial balance</h3>
<form method="get" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin-bottom:16px"><div class="field" style="margin:0"><label>From</label><input type="date" name="from" value="{{ $from }}"></div><div class="field" style="margin:0"><label>To</label><input type="date" name="to" value="{{ $to }}"></div><button class="btn ghost" type="submit">Apply</button></form>
<table style="width:100%;border-collapse:collapse"><thead><tr style="text-align:left"><th style="padding:10px">Code</th><th style="padding:10px">Account</th><th style="padding:10px">Debit</th><th style="padding:10px">Credit</th></tr></thead><tbody>
@php($tbDebit=0) @php($tbCredit=0)
@foreach($trialBalance as $row)
@php($tbDebit += $row->debit_minor) @php($tbCredit += $row->credit_minor)
<tr style="border-top:1px solid #e2eeeb"><td style="padding:10px">{{ $row->code }}</td><td style="padding:10px">{{ $row->name }}</td><td style="padding:10px">৳ {{ number_format(intdiv($row->debit_minor,100)) }}.{{ str_pad((string)($row->debit_minor%100),2,'0',STR_PAD_LEFT) }}</td><td style="padding:10px">৳ {{ number_format(intdiv($row->credit_minor,100)) }}.{{ str_pad((string)($row->credit_minor%100),2,'0',STR_PAD_LEFT) }}</td></tr>
@endforeach
<tr style="border-top:2px solid #bfd5d0;font-weight:800"><td colspan="2" style="padding:10px">Total</td><td style="padding:10px">৳ {{ number_format(intdiv($tbDebit,100)) }}.{{ str_pad((string)($tbDebit%100),2,'0',STR_PAD_LEFT) }}</td><td style="padding:10px">৳ {{ number_format(intdiv($tbCredit,100)) }}.{{ str_pad((string)($tbCredit%100),2,'0',STR_PAD_LEFT) }}</td></tr>
</tbody></table></div>

<div class="wscard" style="margin-top:16px;overflow:auto"><h3>Journals</h3><table style="width:100%;border-collapse:collapse"><thead><tr style="text-align:left"><th style="padding:10px">Date</th><th style="padding:10px">Number</th><th style="padding:10px">Reference</th><th style="padding:10px">Status</th><th style="padding:10px">Integrity</th><th></th></tr></thead><tbody>
@forelse($journals as $journal)<tr style="border-top:1px solid #e2eeeb"><td style="padding:10px">{{ $journal->journal_date->format('d M Y') }}</td><td style="padding:10px">{{ $journal->number }}</td><td style="padding:10px">{{ $journal->reference ?: '—' }}</td><td style="padding:10px">{{ ucfirst($journal->status) }}</td><td style="padding:10px;font-family:monospace">{{ $journal->integrity_hash ? substr($journal->integrity_hash,0,12).'…' : '—' }}</td><td style="padding:10px">@if($journal->status==='draft' && auth()->user()->hasPermission('accounting.manage',$organization))<form method="post" action="{{ route('workspace.accounting.journals.post',$journal) }}">@csrf @method('PATCH')<button class="btn ghost" style="min-height:36px" type="submit">Post</button></form>@endif</td></tr>@empty<tr><td colspan="6" class="muted" style="padding:16px">No journal yet.</td></tr>@endforelse
</tbody></table></div>

<div class="wscard" style="margin-top:16px;overflow:auto"><h3>Chart of accounts</h3><table style="width:100%;border-collapse:collapse"><thead><tr style="text-align:left"><th style="padding:10px">Code</th><th style="padding:10px">Name</th><th style="padding:10px">Type</th><th style="padding:10px">Normal balance</th></tr></thead><tbody>
@forelse($accounts as $account)<tr style="border-top:1px solid #e2eeeb"><td style="padding:10px">{{ $account->code }}</td><td style="padding:10px">{{ $account->name }}</td><td style="padding:10px">{{ ucfirst($account->type) }}</td><td style="padding:10px">{{ ucfirst($account->normal_balance) }}</td></tr>@empty<tr><td colspan="4" class="muted" style="padding:16px">No chart of accounts yet.</td></tr>@endforelse
</tbody></table></div>
</main>
@endsection
