<?php

/*
 * Maps the fixed fields of the trusted section templates to meaningful roles.
 * Editors never see or change this; it is what connects pages to their placements.
 */
return [
    // Page types that appear in a listing, and where their automatic card lives.
    'listings' => [
        'service' => [
            'label' => 'Service',
            'page_key' => 'services',
            'section_template' => 'website.generated.services-s2',
            'group' => 'g-1',
            'template_page' => 'service-exam',
            'slug_prefix' => 'service-details.html?service=',
        ],
        'article' => [
            'label' => 'Article',
            'page_key' => 'articles',
            'section_template' => 'website.generated.articles-s2',
            'group' => 'g-2',
            'filter_group' => 'g-1',
            'template_page' => 'article-1',
            'slug_prefix' => 'article-details.html?article=',
        ],
    ],

    // template:group => role => field. Other groups only follow their first link field.
    'placements' => [
        'website.generated.services-s2:g-1' => ['link' => 'f7', 'title' => 'f5', 'description' => 'f6', 'image' => 'f3', 'image_alt' => 'f4'],
        'website.generated.articles-s2:g-2' => ['link' => 'f10', 'title' => 'f8', 'description' => 'f9', 'image' => 'f3', 'image_alt' => 'f4', 'category' => 'f7', 'category_value' => 'f2'],
        'website.generated.index-s3:g-1' => ['link' => 'f2', 'title' => 'f4', 'description' => 'f5', 'image' => 'f3'],
        'website.generated.article-*-s1:g-2' => ['link' => 'f1', 'image' => 'f2', 'image_alt' => 'f3', 'title' => 'f4'],
        'website.empty:slides' => ['link' => 'link', 'image' => 'image', 'title' => 'title', 'description' => 'copy'],
        'website.header:menu' => ['link' => 'link', 'title' => 'label'],
        'website.header:menu-child' => ['link' => 'link', 'title' => 'label'],
    ],

    // Detail-page fields filled from the creation form. Remaining copy stays as a draft to review.
    'detail_fields' => [
        'website.generated.service-*-s1' => ['title' => 'f2', 'description' => 'f3'],
        'website.generated.service-*-s2' => ['image' => 'f1', 'image_alt' => 'f2', 'title' => 'f4', 'description' => 'f5'],
        'website.generated.article-*-s1' => ['title' => 'f1', 'image' => 'f2', 'image_alt' => 'f3', 'description' => 'f4'],
    ],

    // Phrases the original source used for text waiting on owner-approved content.
    'provisional_phrases' => [
        'Placeholder',
        'سيتم إضافة',
        'مساحة مخصصة',
        'قسم مخصص',
        'ويمكن إضافة المحتوى',
        'يتم استبدال الخريطة',
        'أضف العنوان الدقيق',
        'بعد اعتماد',
    ],

    // Where editors change the destinations the owner still has to supply.
    'destinations' => [
        ['label' => 'Social links (header)', 'section' => 'global-header', 'note' => 'Facebook, Instagram, TikTok and YouTube links'],
        ['label' => 'Social links (footer)', 'section' => 'global-footer', 'note' => 'Facebook, Instagram, TikTok and YouTube links'],
        ['label' => 'Clinic video destination', 'section' => 'about-s2', 'note' => 'Play button on the clinic photo'],
        ['label' => 'Home media link', 'section' => 'index-s5', 'note' => 'Media card button on the home page'],
        ['label' => 'Map block and destination', 'section' => 'contact-s3', 'note' => 'Map caption and optional map link'],
        ['label' => 'Directions link', 'section' => 'contact-s3', 'note' => 'Directions button beside the map'],
        ['label' => 'Promotional calls to action', 'section' => 'global-header', 'note' => 'Header booking button'],
        ['label' => 'Floating contact buttons', 'section' => 'global-floating', 'note' => 'WhatsApp and call shortcuts'],
        ['label' => 'Clinic content', 'section' => 'about-s2', 'note' => 'Clinic photo, description and features'],
        ['label' => 'Testimonials', 'section' => 'index-s6', 'note' => 'Patient reviews on the home page'],
    ],
];
