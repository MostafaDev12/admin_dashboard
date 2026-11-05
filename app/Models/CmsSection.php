<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsSection extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['content' => 'array', 'schema' => 'array', 'groups' => 'array', 'is_visible' => 'boolean'];

    public function page()
    {
        return $this->belongsTo(CmsPage::class, 'page_id');
    }

    public function items()
    {
        return $this->hasMany(CmsItem::class, 'section_id')->orderBy('position')->orderBy('id');
    }
}
