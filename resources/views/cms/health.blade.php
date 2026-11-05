@extends('cms.layout')
@section('title','Content health')
@section('content')
@php
$titles = [
    'missing_media' => ['Missing images', 'danger', 'These fields point to a file that does not exist. Visitors see a blank or fallback area until an approved image is selected.'],
    'broken_links' => ['Broken internal links', 'danger', 'These links point to a page that no longer exists.'],
    'unpublished_links' => ['Links to unpublished pages', 'warning', 'Buttons inside sections that point to an unpublished page. Visitors are sent to the nearest live page until it is published again or a new destination is chosen.'],
    'placeholder_links' => ['Placeholder links', 'warning', 'These links go nowhere until a real destination is entered.'],
    'provisional_text' => ['Provisional text', 'warning', 'Original placeholder wording waiting for owner-approved content.'],
    'detached' => ['Hidden because their page was removed', 'info', 'Their text is preserved. Restore the page, choose a new destination, or delete them.'],
    'hidden_by_page' => ['Hidden while their page is unpublished', 'info', 'These reappear automatically when the page is published again.'],
];
@endphp
@if($missingImages)
<div class="card border-danger">
    <div class="card-header"><h5 class="mb-0">Replace a missing image everywhere</h5></div>
    <div class="card-body">
        <p>Upload the approved image in the <a href="{{ route('cms.media') }}">media library</a>, then select it here. It replaces the missing file in every location listed below.</p>
        @foreach($missingImages as $path => $count)
        <form method="POST" action="{{ route('cms.health.replace-image') }}" class="row g-2 align-items-end mb-2">
            @csrf
            <input type="hidden" name="from" value="{{ $path }}">
            <div class="col-md-5"><label class="form-label">Missing file ({{ $count }} location{{ $count === 1 ? '' : 's' }})</label><input class="form-control" dir="ltr" value="{{ $path }}" disabled></div>
            <div class="col-md-5"><label class="form-label">Replacement from the media library</label><select class="form-select" name="to" required><option value="">Select an image</option>@foreach($images as $image)<option value="{{ $image->path }}">{{ $image->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Replace</button></div>
        </form>
        @endforeach
    </div>
</div>
@endif
@foreach($titles as $key => [$title, $color, $help])
    @if(count($report[$key]))
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">{{ $title }}</h5><span class="badge bg-{{ $color }}">{{ count($report[$key]) }}</span></div>
        <div class="card-body pb-0"><p class="text-muted">{{ $help }}</p></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Location</th><th>Field</th><th>Current value</th><th></th></tr></thead>
                <tbody>
                @foreach($report[$key] as $row)
                    <tr>
                        <td dir="auto"><small>{{ $row['location'] }}</small></td>
                        <td dir="auto"><small>{{ $row['field'] }}</small></td>
                        <td dir="auto"><small>{{ \Illuminate\Support\Str::limit($row['value'], 90) }}</small></td>
                        <td class="text-end"><a class="btn btn-sm btn-soft-primary" href="{{ $row['url'] }}">Edit</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endforeach
<div class="card">
    <div class="card-header"><h5 class="mb-0">Where to update owner-supplied details</h5></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <tbody>
            @foreach($destinations as $row)
                <tr><td>{{ $row['label'] }}</td><td class="text-muted"><small>{{ $row['note'] }}</small></td><td class="text-end">@if($row['url'])<a class="btn btn-sm btn-soft-primary" href="{{ $row['url'] }}">Open editor</a>@endif</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
