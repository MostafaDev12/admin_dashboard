<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebsiteContactRequest;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteController extends Controller
{
    public function show(Request $request, string $path = '')
    {
        $slug = ltrim($path, '/');
        if ($slug === 'index.html') {
            $slug = '';
        }
        if ($slug === 'service-details.html') {
            $selector = $request->query('service', 'exam');
            abort_unless(is_string($selector) && preg_match('/^[a-zA-Z0-9_-]{1,150}$/D', $selector), 404);
            $slug .= '?service='.$selector;
        }
        if ($slug === 'article-details.html') {
            $selector = $request->query('article', '1');
            abort_unless(is_string($selector) && preg_match('/^[a-zA-Z0-9_-]{1,150}$/D', $selector), 404);
            $slug .= '?article='.$selector;
        }
        $redirect = DB::table('cms_redirects')->where('from', $slug)->value('to');
        if ($redirect !== null) {
            return redirect(url($redirect), 301);
        }
        $page = CmsPage::with('sections.items')->where('slug', $slug)->where('is_visible', true)->firstOrFail();
        $globals = CmsSection::with('items')->whereNull('page_id')->get()->keyBy('key');
        app(\App\Services\WebsiteContent::class)->apply($page, $globals);
        $mediaAlts = \App\Models\CmsMedia::whereNotNull('alt')->where('alt', '!=', '')->pluck('alt', 'path');

        return response()->view('website.page', compact('page', 'globals', 'mediaAlts'));
    }

    public function contact(WebsiteContactRequest $request)
    {
        $data = $request->validated();
        CmsSubmission::create(collect($data)->only(['name', 'phone', 'email'])->all() + ['details' => collect($data)->except(['name', 'phone', 'email'])->all()]);
        if ($request->expectsJson()) {
            return response()->json(['message' => 'تم إرسال طلبك بنجاح. سنتواصل معك لتأكيد الموعد.'], 201);
        }

        return back()->with('website_success', 'تم إرسال طلبك بنجاح. سنتواصل معك لتأكيد الموعد.');
    }

    public function sitemap()
    {
        $pages = CmsPage::where('is_visible', true)->get()->reject(fn ($p) => $p->seo['noindex'] ?? false);

        return response()->view('website.sitemap', compact('pages'))->header('Content-Type', 'application/xml');
    }
}
