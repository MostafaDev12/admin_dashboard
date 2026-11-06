<div data-media-field>
<input class="form-control" readonly id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" aria-label="Selected media">
<img id="preview-{{ $id }}" src="{{ $value }}" alt="Selected image preview" class="img-thumbnail my-2" style="max-height:120px" @if(!$value) hidden @endif>
<div class="d-flex flex-wrap gap-1"><button type="button" class="btn btn-sm btn-soft-primary" data-media-target="{{ $id }}">Choose / replace from library</button><button type="button" class="btn btn-sm btn-soft-success" data-media-target="{{ $id }}" data-open-upload>Upload new image</button><button type="button" class="btn btn-sm btn-soft-danger" data-media-remove="{{ $id }}">Remove selection</button></div>
<div class="form-text">The selection is applied when you save this content form.</div>
</div>
