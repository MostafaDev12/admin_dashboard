<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsMedia extends Model
{
    public function getDimensionsAttribute(): ?array
    {
        if (! str_starts_with($this->mime ?? '', 'image/')) {
            return null;
        }
        $path = realpath(public_path(ltrim($this->path, '/')));
        $roots = [realpath(public_path('website/assets')), realpath(storage_path('app/public/cms'))];
        if (! $path || ! collect($roots)->filter()->contains(fn ($root) => str_starts_with(strtolower($path), strtolower($root).DIRECTORY_SEPARATOR))) {
            return null;
        }
        $size = @getimagesize($path);

        return $size ? ['width' => $size[0], 'height' => $size[1]] : null;
    }

    protected $table = 'cms_media';

    protected $guarded = ['id'];

    protected $casts = ['is_original' => 'boolean'];
}
