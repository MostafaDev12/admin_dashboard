@extends('cms.layout')
@section('title','Trash')
@section('content')
<div class="card">
    <div class="card-body pb-0"><p class="text-muted">Removed pages, sections, and items are kept here until an owner deletes them permanently. Restoring brings back the content exactly as it was, including its position and links.</p></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>Content</th><th>Type</th><th>Removed</th><th>By</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td dir="auto">{{ $entry->label }}</td>
                    <td><span class="badge bg-{{ $entry->type === 'archive' ? 'info' : 'secondary' }}">{{ $entry->type === 'archive' ? 'Upgrade archive' : ucfirst($entry->type) }}</span></td>
                    <td>{{ $entry->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $entry->deletedBy?->name ?? 'System' }}</td>
                    <td>
                        <div class="d-flex gap-2 justify-content-end">
                            @if($entry->type !== 'archive')
                                <form method="POST" action="{{ route('cms.trash.restore', $entry) }}">@csrf<button class="btn btn-sm btn-soft-success">Restore</button></form>
                            @endif
                            @if(auth('admin')->user()->IsSuper())
                                <form method="POST" action="{{ route('cms.trash.destroy', $entry) }}" data-confirm="Permanently delete this? It cannot be recovered afterwards.">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger">Delete permanently</button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted text-center py-4">Trash is empty.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $entries->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
