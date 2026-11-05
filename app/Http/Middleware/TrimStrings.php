<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected function transform($key, $value)
    {
        // CMS text nodes include significant spaces around inline spans and icons.
        if (str_starts_with($key, 'content.') && request()->is('admin/cms/*')) {
            return $value;
        }

        return parent::transform($key, $value);
    }
}
