<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebsiteContactRequest;
use App\Models\CmsPage;
use App\Models\CmsSection;
use App\Models\CmsSubmission;
use App\Services\CmsMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsiteController extends Controller
{
    public function show(Request $request, string $path = '')
    {
        $slug = ltrim($path, '/');
        if ($slug === 'index.html' && ! app(\App\Services\CmsUrls::class)->enabled()) {
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
        $redirect = DB::table('cms_redirects')->where('from', $slug)->first();
        if ($redirect) {
            abort_unless(CmsPage::where('slug', $redirect->to)->where('is_visible', true)->exists(), 404);
            $query = $request->query();
            if ($path === 'service-details.html') {
                unset($query['service']);
            }
            if ($path === 'article-details.html') {
                unset($query['article']);
            }

            return redirect(url($redirect->to).($query ? '?'.http_build_query($query) : ''), $redirect->status ?? 301);
        }
        $page = CmsPage::with('sections.items')->where('slug', $slug)->where('is_visible', true)->firstOrFail();
        $globals = CmsSection::with('items')->whereNull('page_id')->get()->keyBy('key');
        app(\App\Services\WebsiteContent::class)->apply($page, $globals);
        $mediaAlts = \App\Models\CmsMedia::whereNotNull('alt')->where('alt', '!=', '')->pluck('alt', 'path');

        return response()->view('website.page', compact('page', 'globals', 'mediaAlts'));
    }

    public function contact(WebsiteContactRequest $request, CmsMessages $messages)
    {
        $data = $request->validated();
        CmsSubmission::create(collect($data)->only(['name', 'phone', 'email'])->all() + ['details' => collect($data)->except(['name', 'phone', 'email'])->all()]);
        if ($request->expectsJson()) {
            return response()->json(['message' => $messages->get('appointment_success')], 201);
        }

        return back()->with('website_success', $messages->get('appointment_success'));
    }

    public function sitemap()
    {
        $pages = CmsPage::where('is_visible', true)->get()->reject(fn ($p) => $p->seo['noindex'] ?? false);

        return response()->view('website.sitemap', compact('pages'))->header('Content-Type', 'application/xml');
    }
}
