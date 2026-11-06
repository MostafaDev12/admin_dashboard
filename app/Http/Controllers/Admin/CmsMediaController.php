<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsMedia;
use App\Services\CmsMediaUsage;
use Illuminate\Http\Request;

class CmsMediaController extends Controller
{
    public function picker(Request $request)
    {
        $media = CmsMedia::where('mime', 'like', 'image/%')->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%')->orWhere('alt', 'like', '%'.$request->string('q').'%')))->orderByDesc('id')->paginate(48);

        return response()->json($media);
    }

    public function show(Request $request, CmsMedia $media, CmsMediaUsage $usage)
    {
        $locations = $usage->locations($media->path);

        return view('cms.media-detail', compact('media', 'locations'));
    }

    private function source(Request $request, CmsMediaUsage $usage): CmsMedia
    {
        $data = $request->validate(['source' => 'required|string|max:500']);
        abort_unless($usage->normalize($data['source']) && $usage->locations($data['source']), 422, 'Select an image referenced by existing content.');

        return CmsMedia::where('path', $data['source'])->first() ?? new CmsMedia(['path' => $data['source'], 'name' => basename($data['source']), 'mime' => 'image/jpeg']);
    }

    public function preview(Request $request, CmsMediaUsage $usage, ?CmsMedia $media = null)
    {
        $media = $media ?? $this->source($request, $usage);
        $data = $request->validate(['target' => 'required|exists:cms_media,path', 'mode' => 'required|in:one,all', 'location' => 'nullable|string']);
        $target = CmsMedia::where('path', $data['target'])->where('mime', 'like', 'image/%')->firstOrFail();
        abort_if($target->path === $media->path, 422, 'Choose a different image.');
        $locations = collect($usage->locations($media->path))->filter(fn ($row) => $row['editable'])->all();
        if ($data['mode'] === 'one') {
            $selected = $locations[$data['location'] ?? ''] ?? null;
            abort_if(($selected['shared_setting'] ?? false) && count($locations) > 1, 422, 'This shared setting affects other locations. Choose Replace everywhere to review them, or select one dependent content field.');
            $locations = array_intersect_key($locations, array_flip([$data['location'] ?? '']));
        }
        abort_unless($locations, 422, 'Select an editable content location.');
        $token = $usage->token($media->path, $target->path, $locations);

        return view('cms.media-confirm', compact('media', 'target', 'locations', 'token'));
    }

    public function replace(Request $request, CmsMediaUsage $usage, ?CmsMedia $media = null)
    {
        $media = $media ?? $this->source($request, $usage);
        $data = $request->validate(['target' => 'required|exists:cms_media,path', 'locations' => 'required|array|min:1', 'locations.*' => 'required|string', 'token' => 'required|string', 'confirmed' => 'accepted']);
        abort_unless(CmsMedia::where('path', $data['target'])->where('mime', 'like', 'image/%')->exists(), 422);
        $count = $usage->replace($media->path, $data['target'], $data['locations'], $data['token']);

        return ($media->exists ? redirect()->route('cms.media.show', $media) : redirect()->route('cms.media'))->with('success', "Replaced {$count} selected content references. Original files were preserved.");
    }
}
