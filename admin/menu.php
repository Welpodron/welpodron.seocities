<?

return [
    'parent_menu' => 'welpodron',
    'text' => 'SEO города (welpodron.seocities)',
    'items' => [
        [
            'text' => 'Города',
            'url' => 'welpodron.seocities.cities.items.php?lang=' . LANGUAGE_ID,
            "icon" => "translate_menu_icon",
        ],
        [
            'text' => 'SEO',
            'url' => 'welpodron.seocities.seo.items.php?lang=' . LANGUAGE_ID,
            "icon" => "seo_menu_icon",
        ],
    ],
];
