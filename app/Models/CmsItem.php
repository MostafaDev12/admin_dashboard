<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['content' => 'array', 'schema' => 'array', 'overrides' => 'array', 'is_visible' => 'boolean', 'is_managed' => 'boolean'];

    protected static function booted(): void
    {
        static::created(function (self $item): void {
            if ($item->import_key !== null) {
                return;
            }
            $content = $item->content;
            foreach ($item->schema as $key => $field) {
                if ($field['type'] === 'fixed' && ($field['label'] ?? '') === 'id' && ! empty($content[$key])) {
                    $content[$key] = $content[$key].'-cms-'.$item->id;
                }
            }
            if ($content !== $item->content) {
                $item->updateQuietly(['content' => $content]);
            }
        });
    }

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
