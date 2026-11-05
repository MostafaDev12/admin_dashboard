<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['assets' => 'array', 'seo' => 'array', 'is_visible' => 'boolean'];

    public function sections()
    {
        return $this->hasMany(CmsSection::class, 'page_id')->orderBy('position')->orderBy('id');
    }

    public function getPublicUrlAttribute(): string
    {
        return url($this->slug);
    }
}
