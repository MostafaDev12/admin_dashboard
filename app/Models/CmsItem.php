<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['content' => 'array', 'schema' => 'array', 'is_visible' => 'boolean'];

    public function section()
    {
        return $this->belongsTo(CmsSection::class, 'section_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
