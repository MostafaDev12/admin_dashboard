<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsMedia;
use App\Models\CmsSection;
use App\Services\CmsContentHealth;
use App\Services\CmsMessages;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CmsHealthController extends Controller
{
    public function index(CmsContentHealth $health)
    {
        return view('cms.health', [
            'report' => $health->report(),
            'destinations' => $health->destinations(),
            'missingImages' => $health->missingImages(),
            'images' => $health->replaceableImages(),
        ]);
    }

    /** Lets an owner-approved upload replace a missing or outdated image everywhere it is used. */
    public function replaceImage(Request $request, CmsContentHealth $health)
    {
        $data = $request->validate([
            'from' => 'required|string|max:500',
            'to' => ['required', 'string', Rule::exists('cms_media', 'path')],
        ]);
        abort_unless(str_starts_with((string) CmsMedia::where('path', $data['to'])->value('mime'), 'image/'), 422, 'Select an image.');
        $count = $health->replaceImage($data['from'], $data['to']);

        return back()->with('success', "Image replaced in {$count} location(s).");
    }

    public function messages()
    {
        $section = CmsSection::where('key', CmsMessages::SECTION)->first();
        abort_unless($section, 409, 'Run website:upgrade to enable managed messages.');

        return redirect()->route('cms.sections.edit', $section);
    }
}
