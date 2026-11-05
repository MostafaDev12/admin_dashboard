<?php

namespace App\Services;

use App\Models\CmsSection;
use Throwable;

/**
 * Customer-facing system copy. Stored as ordinary section content so editors manage it without
 * code. Always plain text: every output path escapes it or assigns it with textContent.
 */
class CmsMessages
{
    public const SECTION = 'global-messages';

    /** key => [admin label, default text, available to the public form script] */
    private const MESSAGES = [
        'appointment_success' => ['Appointment request sent', 'تم إرسال طلبك بنجاح. سنتواصل معك لتأكيد الموعد.', false],
        'submit_error' => ['Form could not be submitted', 'تعذر إرسال الطلب.', true],
        'network_error' => ['Connection problem', 'تعذر الاتصال. يرجى المحاولة مرة أخرى.', true],
        'session_expired' => ['Page expired, reload needed', 'انتهت صلاحية الصفحة. يرجى تحديث الصفحة والمحاولة مرة أخرى.', true],
        'rate_limited' => ['Too many attempts', 'عدد المحاولات كبير. يرجى الانتظار قليلًا ثم المحاولة مرة أخرى.', true],
        'validation_name_required' => ['Name is missing', 'يرجى إدخال الاسم.', false],
        'validation_name_long' => ['Name is too long', 'الاسم طويل جدًا.', false],
        'validation_phone_required' => ['Phone number is missing', 'يرجى إدخال رقم الموبايل.', false],
        'validation_phone_invalid' => ['Phone number is not valid', 'يرجى إدخال رقم موبايل صحيح.', false],
        'validation_email_invalid' => ['Email address is not valid', 'يرجى إدخال بريد إلكتروني صحيح.', false],
        'validation_age_invalid' => ['Age is not valid', 'يرجى إدخال سن صحيح.', false],
        'validation_date_invalid' => ['Date is not valid', 'يرجى اختيار تاريخ صحيح.', false],
        'validation_date_past' => ['Date is in the past', 'يرجى اختيار تاريخ اليوم أو تاريخ لاحق.', false],
        'validation_too_long' => ['Text is too long', 'النص المدخل طويل جدًا.', false],
        'error_404' => ['Page not found', 'الصفحة غير موجودة.', false],
        'error_419' => ['Page expired', 'انتهت صلاحية الصفحة. يرجى تحديث الصفحة.', false],
        'error_429' => ['Too many requests', 'عدد الطلبات كبير. يرجى المحاولة بعد قليل.', false],
        'error_500' => ['Server error', 'حدث خطأ غير متوقع. يرجى المحاولة لاحقًا.', false],
        'error_503' => ['Site temporarily unavailable', 'الموقع غير متاح مؤقتًا. يرجى المحاولة لاحقًا.', false],
    ];

    private ?array $content = null;

    public function get(string $key): string
    {
        $value = $this->stored()[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : (self::MESSAGES[$key][1] ?? '');
    }

    /** Only what the public form script needs, safe to embed with Blade's @json. */
    public function forBrowser(): array
    {
        $messages = [];
        foreach (self::MESSAGES as $key => $definition) {
            if ($definition[2]) {
                $messages[$key] = $this->get($key);
            }
        }

        return $messages;
    }

    /** Wording for the fixed validation rules in WebsiteContactRequest. */
    public function validation(): array
    {
        return [
            'name.required' => $this->get('validation_name_required'),
            'name.max' => $this->get('validation_name_long'),
            'phone.required' => $this->get('validation_phone_required'),
            'phone.regex' => $this->get('validation_phone_invalid'),
            'phone.max' => $this->get('validation_phone_invalid'),
            'email.email' => $this->get('validation_email_invalid'),
            'email.max' => $this->get('validation_too_long'),
            'age.integer' => $this->get('validation_age_invalid'),
            'age.min' => $this->get('validation_age_invalid'),
            'age.max' => $this->get('validation_age_invalid'),
            'date.date' => $this->get('validation_date_invalid'),
            'date.after_or_equal' => $this->get('validation_date_past'),
            'complaint.max' => $this->get('validation_too_long'),
            'details.max' => $this->get('validation_too_long'),
            'service.max' => $this->get('validation_too_long'),
            'preferred_time.max' => $this->get('validation_too_long'),
        ];
    }

    /** Creates the settings record and adds new messages, keeping anything already edited. */
    public function ensureSection(): CmsSection
    {
        $schema = [];
        $defaults = [];
        foreach (self::MESSAGES as $key => [$label, $text]) {
            $schema[$key] = ['type' => 'message', 'label' => $label];
            $defaults[$key] = $text;
        }
        $section = CmsSection::firstOrCreate(['key' => self::SECTION], ['label' => 'Form & system messages', 'template' => 'website.empty', 'content' => $defaults, 'schema' => $schema, 'groups' => []]);
        $merged = ($section->content ?? []) + $defaults;
        if ($merged != $section->content || $schema != $section->schema) {
            $section->update(['content' => $merged, 'schema' => $schema]);
        }

        return $section;
    }

    private function stored(): array
    {
        if ($this->content === null) {
            try {
                $section = CmsSection::where('key', self::SECTION)->where('is_visible', true)->first();
                $this->content = $section?->content ?? [];
            } catch (Throwable) {
                // Error pages must still render when the database is what failed.
                $this->content = [];
            }
        }

        return $this->content;
    }
}
