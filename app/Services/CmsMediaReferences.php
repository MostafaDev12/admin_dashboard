<?php

namespace App\Services;

class CmsMediaReferences
{
    public function isUsed(string $path): bool
    {
        return count(app(CmsMediaUsage::class)->locations($path)) > 0;
    }
}
