<?

use Bitrix\Main\Loader;

Loader::includeModule("welpodron.core");

Loader::registerAutoLoadClasses(
    'welpodron.seocities',
    [
        'Welpodron\SeoCities\CityTable' => 'lib/db/city.php',
        'Welpodron\SeoCities\SeoTable' => 'lib/db/seo.php',
        'Welpodron\SeoCities\Utils' => 'lib/utils/utils.php',
    ]
);
