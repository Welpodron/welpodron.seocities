<?

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    'welpodron.seocities',
    [
        'Welpodron\SeoCities\CityTable' => 'lib/db/city.php',
        'Welpodron\SeoCities\SeoTable' => 'lib/db/seo.php',
        'Welpodron\SeoCities\Types' => 'lib/db/types.php',
        'Welpodron\SeoCities\Utils\Buffer' => 'lib/utils/buffer.php',
        'Welpodron\SeoCities\Utils' => 'lib/utils/utils.php',
    ]
);
