@extends('layouts.workspace')
@section('title','New journal — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.accounting.index') }}">Back</a></div></nav>
<main class="wsmain"><div style="max-width:980px"><div class="eyebrow">Double-entry accounting</div><h2>New journal draft</h2>
<form class="wscard" method="post" action="{{ route('workspace.accounting.journals.store') }}">@csrf
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px"><div class="field"><label>Date</label><input type="date" name="journal_date" value="{{ old('journal_date',date('Y-m-d')) }}" required></div><div class="field"><label>Reference</label><input name="reference" value="{{ old('reference') }}"></div></div>
<div class="field"><label>Description</label><input name="description" value="{{ old('description') }}"></div>
@error('rows')<div class="error">{{ $message }}</div>@enderror
<div style="overflow:auto"><table style="width:100%;min-width:880px;border-collapse:collapse"><thead><tr><th style="padding:8px;text-align:left">Account</th><th style="padding:8px;text-align:left">Line description</th><th style="padding:8px;text-align:left">Debit BDT</th><th style="padding:8px;text-align:left">Credit BDT</th></tr></thead><tbody>
@for($i=0;$i<8;$i++)<tr><td style="padding:8px"><select name="rows[{{ $i }}][account_id]"><option value="">Select</option>@foreach($accounts as $account)<option value="{{ $account->id }}" @selected(old("rows.$i.account_id")==$account->id)>{{ $account->code }} — {{ $account->name }}</option>@endforeach</select></td><td style="padding:8px"><input name="rows[{{ $i }}][description]" value="{{ old("rows.$i.description") }}"></td><td style="padding:8px"><input name="rows[{{ $i }}][debit]" value="{{ old("rows.$i.debit") }}"></td><td style="padding:8px"><input name="rows[{{ $i }}][credit]" value="{{ old("rows.$i.credit") }}"></td></tr>@endfor
</tbody></table></div>
<button class="btn primary" style="border:0;margin-top:18px" type="submit">Save journal draft</button>
</form></div></main>
@endsection
