<?php

namespace App\Support;

/**
 * Plain-language names for the compiler's technical labels. Display only: stored schema and
 * editor labels are not changed, and anything without a mapping is shown as it is.
 */
class CmsLabels
{
    private const ATTRIBUTES = [
        'href' => 'Link',
        'src' => 'Image',
        'alt' => 'Image description',
        'placeholder' => 'Placeholder text',
        'aria-label' => 'Screen reader label',
        'data-category' => 'Filter category',
    ];

    private const GROUPS = [
        'top-social' => 'Social links',
        'top-contact' => 'Contact links',
        'menu-toggle' => 'Menu button bars',
        'footer-social' => 'Social links',
        'container footer-grid' => 'Footer columns',
        'footer-col' => 'Footer links',
        'footer-col footer-contact' => 'Footer contact links',
        'hero-badges' => 'Highlights',
        'container trust-grid' => 'Trust items',
        'expand-services' => 'Service cards',
        'reviews-grid' => 'Patient reviews',
        'contact-stack' => 'Contact links',
        'about-points' => 'Highlights',
        'clinic-features' => 'Clinic features',
        'contact-cards' => 'Contact cards',
        'select' => 'Form options',
        'breadcrumbs' => 'Breadcrumb items',
        'services-grid' => 'Service cards',
        'why-grid' => 'Reasons to choose the clinic',
        'journey-grid' => 'Visit steps',
        'articles-grid' => 'Article cards',
        'contact-list' => 'Contact links',
        'location-features' => 'Location details',
        'details-points' => 'Key points',
        'container details-info-grid' => 'Information blocks',
        'article-main__meta' => 'Article details',
        'article-sidebar' => 'Related articles',
    ];

    private const SECTIONS = [
        'header' => 'Header',
        'footer' => 'Footer',
        'floating' => 'Floating contact buttons',
        'trust-strip' => 'Trust strip',
        'section section--white services-preview' => 'Services preview',
    ];

    /** The field's purpose, with the original value or text that identifies it. */
    public static function field(array $field): string
    {
        $label = trim((string) ($field['label'] ?? ''));
        if (! str_contains($label, '·')) {
            return $label;
        }
        [$attribute, $detail] = array_map('trim', explode('·', $label, 2));
        $name = self::ATTRIBUTES[$attribute] ?? $attribute;

        return $detail === '' ? $name : $name.' — '.$detail;
    }

    /** Only the purpose, for tables that show the current value in its own column. */
    public static function purpose(array $field): string
    {
        $label = trim((string) ($field['label'] ?? ''));
        if (! str_contains($label, '·')) {
            return $label !== '' ? $label : ucfirst((string) ($field['type'] ?? 'Field'));
        }
        $attribute = trim(explode('·', $label, 2)[0]);

        return self::ATTRIBUTES[$attribute] ?? $attribute;
    }

    public static function group(?string $label): string
    {
        $label = trim((string) $label);

        return self::GROUPS[trim(explode('·', $label, 2)[0])] ?? $label;
    }

    public static function section(?string $label): string
    {
        $label = trim((string) $label);

        return self::SECTIONS[$label] ?? $label;
    }
}
