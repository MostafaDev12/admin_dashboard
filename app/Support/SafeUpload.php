<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Upload handling for the retained legacy modules. Stored names are generated and take their
 * extension from the detected content, so a client cannot choose what the web server executes.
 */
class SafeUpload
{
    public const IMAGE_RULE = 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:5120';

    public const ICON_RULE = 'nullable|file|mimes:jpeg,jpg,png,webp,gif,ico|max:2048';

    public const VIDEO_RULE = 'nullable|file|mimes:mp4,webm|max:51200';

    public static function move(UploadedFile $file, string $directory): string
    {
        $name = Str::uuid().'.'.$file->extension();
        $file->move(public_path($directory), $name);

        return $name;
    }

    public static function delete(string $directory, ?string $name): void
    {
        // Stored values predate these rules, so never trust them as a path.
        $name = basename((string) $name);
        if ($name === '' || $name === '.' || $name === '..') {
            return;
        }
        $path = public_path($directory.'/'.$name);
        if (is_file($path)) {
            unlink($path);
        }
    }
}
