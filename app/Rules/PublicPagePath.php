<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PublicPagePath implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === '' || $value === null) {
            return;
        }
        $path = explode('?', $value, 2)[0];
        $reserved = ['admin', 'api', 'login', 'logout', 'register', 'password', 'appointment', 'index', 'build', 'storage', 'website'];
        $valid = preg_match('~^[a-zA-Z0-9_-]+(?:/[a-zA-Z0-9_-]+)*(?:\.html)?(?:\?(?:service|article)=[a-zA-Z0-9_-]+)?$~D', $value)
            && ! in_array(strtolower(explode('/', $path)[0]), $reserved, true)
            && strtolower($path) !== 'index.html';
        if (str_contains($value, '?')) {
            $valid = $valid && (preg_match('~^service-details\.html\?service=[a-zA-Z0-9_-]+$~D', $value)
                || preg_match('~^article-details\.html\?article=[a-zA-Z0-9_-]+$~D', $value));
        } elseif (in_array($path, ['service-details.html', 'article-details.html'], true)) {
            $valid = false;
        }
        if ($valid) {
            try {
                $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create('/'.$path, 'GET'));
                $valid = $route->getName() === 'website.page' || (app(\App\Services\CmsUrls::class)->enabled() && str_starts_with((string) $route->getName(), 'website.') && ! in_array($route->getName(), ['website.sitemap', 'website.contact']));
            } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $valid = false;
            }
        }
        if (! $valid) {
            $fail('Use a relative public page path without a leading slash, reserved route, or unsupported query string.');
        }
    }
}
