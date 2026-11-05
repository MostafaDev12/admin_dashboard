<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class CmsContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) auth('admin')->user()?->sectionCheck('cms');
    }

    public function rules(): array
    {
        return ['label' => 'required|string|max:190', 'position' => 'required|integer|min:0|max:100000', 'is_visible' => 'required|boolean', 'content' => 'nullable|array', 'target_page_id' => 'nullable|integer|exists:cms_pages,id'];
    }

    public static function isSafeLink(string $value): bool
    {
        return (bool) preg_match('~^(?:https?://[^\s<>"\']+|mailto:[^\s<>"\']+|tel:[+0-9 ()-]+|/[^\s<>"\']*|\#[^\s<>"\']*|[a-zA-Z0-9_-]+\.html(?:[?\#][^\s<>"\']*)?)$~u', $value);
    }

    public function contentFor(array $schema, array $existing = []): array
    {
        $content = [];
        $mediaPaths = \App\Models\CmsMedia::pluck('path')->all();
        foreach ($schema as $key => $field) {
            if ($field['type'] === 'fixed') {
                $content[$key] = $existing[$key] ?? '';

                continue;
            }
            $value = $this->input('content.'.$key, '') ?? '';
            if (! is_string($value) || mb_strlen($value) > 20000) {
                throw ValidationException::withMessages(['content.'.$key => 'Use plain text with at most 20,000 characters.']);
            }
            if ($field['type'] === 'boolean') {
                $value = in_array($value, ['0', 'false'], true) ? '0' : '1';
            }
            if (in_array($field['type'], ['image', 'link']) && $value !== '' && ! ($field['type'] === 'image' && in_array($value, $mediaPaths, true)) && ! self::isSafeLink($value)) {
                throw ValidationException::withMessages(['content.'.$key => 'Choose a website page, media file, anchor, or a valid http(s), mailto, or telephone link.']);
            }
            if ($field['type'] === 'icon' && ! preg_match('/^[a-zA-Z0-9 _-]*$/', $value)) {
                throw ValidationException::withMessages(['content.'.$key => 'Use Font Awesome icon class names.']);
            }
            $content[$key] = $value;
        }

        return $content;
    }
}
