@extends('layouts.workspace')
@section('title','New engagement — '.$organization->name)
@section('body')
<nav class="wsnav"><div class="wsnavin"><div class="wsbrand"><span class="mark">DF</span><span>DhakaFin · {{ $organization->name }}</span></div><a class="btn ghost" href="{{ route('workspace.engagements.index') }}">Back</a></div></nav>
<main class="wsmain"><div style="max-width:760px">
<div class="eyebrow">Service delivery</div><h2>New engagement</h2>
<form class="wscard" method="post" action="{{ route('workspace.engagements.store') }}">@csrf
<div class="field"><label>Service</label><select name="service_type" required>
@foreach(['accounting'=>'Accounting','audit_support'=>'Audit Support','corporate_tax'=>'Corporate Tax','personal_tax'=>'Personal Tax','vat'=>'VAT','vcfo'=>'Virtual CFO','cost_control'=>'Cost & Control Review','other'=>'Other'] as $value=>$label)
<option value="{{ $value }}" @selected(old('service_type')===$value)>{{ $label }}</option>
@endforeach
</select></div>
<div class="field"><label>Title</label><input name="title" value="{{ old('title') }}" required>@error('title')<div class="error">{{ $message }}</div>@enderror</div>
<div class="field"><label>Start date</label><input type="date" name="start_date" value="{{ old('start_date') }}"></div>
<div class="field"><label>Due date</label><input type="date" name="due_date" value="{{ old('due_date') }}">@error('due_date')<div class="error">{{ $message }}</div>@enderror</div>
<button class="btn primary" style="border:0" type="submit">Create engagement</button>
</form></div></main>
@endsection
