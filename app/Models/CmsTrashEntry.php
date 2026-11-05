<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsTrashEntry extends Model
{
    protected $table = 'cms_trash';

    protected $guarded = ['id'];

    protected $casts = ['payload' => 'array'];

    public function deletedBy()
    {
        return $this->belongsTo(Admin::class, 'deleted_by');
    }
}
