<div class="row g-3">
<div class="col-md-6"><label class="form-label">Editor label</label><input class="form-control" name="label" value="{{ old('label',$record->label) }}" required></div><div class="col-md-3"><label class="form-label">Display order</label><input type="number" min="0" max="100000" name="position" class="form-control" value="{{ old('position',$record->position) }}" required></div><div class="col-md-3"><label class="form-label">Visibility</label><select name="is_visible" class="form-select"><option value="1" @selected(old('is_visible',$record->is_visible)==1)>Visible</option><option value="0" @selected(old('is_visible',$record->is_visible)==0)>Hidden</option></select></div>
@foreach($record->schema as $key=>$field)
@continue($field['type']==='fixed')
@php($submitted=old('content.'.$key,$record->content[$key]??''))
@php($value=is_string($submitted) ? $submitted : ($record->content[$key]??''))
<div class="col-md-6"><label class="form-label" for="field-{{ $key }}">{{ $field['label'] }}</label>
@if($field['type']==='text' && (mb_strlen($value)>100 || str_contains($value,"\n")))<textarea dir="auto" rows="4" class="form-control" id="field-{{ $key }}" name="content[{{ $key }}]">{{ $value }}</textarea>
@else<input dir="auto" class="form-control" id="field-{{ $key }}" name="content[{{ $key }}]" value="{{ $value }}" @if($field['type']==='link') list="website-pages" @endif>@endif
@if($field['type']==='image')<select class="form-select mt-2" data-media-select="field-{{ $key }}"><option value="">Select media</option>@foreach($media->filter(fn($m)=>str_starts_with($m->mime,'image/')) as $file)<option value="{{ $file->path }}" @selected($value===$file->path)>{{ $file->name }}</option>@endforeach</select><img id="preview-field-{{ $key }}" src="{{ $value }}" alt="Preview" class="img-thumbnail mt-2" style="max-height:120px" @if(!$value) hidden @endif>@endif
@error('content.'.$key)<div class="text-danger">{{ $message }}</div>@enderror</div>
@endforeach
</div><datalist id="website-pages">@foreach(\App\Models\CmsPage::all() as $linkPage)<option value="/{{ $linkPage->slug }}">{{ $linkPage->title }}</option>@endforeach</datalist>
