@extends('cms.layout')
@section('title','Menus, footer & settings')
@section('content')
<div class="card"><div class="card-body"><p>Shared content appears on every page. Header collections include the desktop and mobile menus, service dropdowns, social links, and contact details. Footer collections include link groups and contact links.</p><table class="table align-middle"><thead><tr><th>Shared area</th><th>Items</th><th></th></tr></thead><tbody>@foreach($sections as $section)<tr><td>{{ $section->label }}</td><td>{{ $section->items_count }}</td><td><a class="btn btn-soft-primary" href="{{ route('cms.sections.edit',$section) }}">Manage</a></td></tr>@endforeach</tbody></table></div></div>
@endsection
