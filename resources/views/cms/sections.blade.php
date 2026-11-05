@extends('cms.layout')
@section('title','Footer & shared settings')
@section('content')
<div class="card"><div class="card-body"><p>Shared content appears on every page. Header collections include social links and contact details; navigation links are managed under Navigation menu. Footer collections include link groups and contact links.</p><table class="table align-middle"><thead><tr><th>Shared area</th><th>Items</th><th></th></tr></thead><tbody>@foreach($sections as $section)<tr><td>{{ \App\Support\CmsLabels::section($section->label) }}</td><td>{{ $section->items_count }}</td><td><a class="btn btn-soft-primary" href="{{ route('cms.sections.edit',$section) }}">Manage</a></td></tr>@endforeach</tbody></table></div></div>
@endsection
