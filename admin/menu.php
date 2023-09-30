<?

return [
    'parent_menu' => 'global_menu_settings', // раздел, где выводить пункт меню
    'text' => 'Настройка параметров модуля welpodron.seocities',
    // Подпункты
    'items' => [
        ['text' => 'Города', 'url' => 'welpodron.seocities.cities.items.php?lang=' . LANGUAGE_ID],
        ['text' => 'SEO', 'url' => 'welpodron.seocities.seo.items.php?lang=' . LANGUAGE_ID],
    ],

];
