<?

namespace Welpodron\SeoCities;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\IO\Path;
use Bitrix\Main\Config\Option;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Query\QueryHelper;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;

use Welpodron\SeoCities\CityTable;
use Welpodron\SeoCities\SeoTable;

class Utils
{
    const DEFAULT_MODULE_ID = 'welpodron.seocities';
    const DEFAULT_DOMAIN = 'DEFAULT';

    public static function replaceDomain($domain, $customHost = null, $customUri = null)
    {
        if ($_SERVER["HTTPS"]) {
            $protocol = "https://";
        } else {
            $protocol = "http://";
        }

        if ($customHost !== null) {
            $host = $customHost;
        } else {
            $host = $_SERVER['HTTP_HOST'];
        }

        $host = filter_var($host, FILTER_SANITIZE_URL);

        $host = ltrim(str_replace($protocol, '', $host));

        $host = preg_replace('(:+\d+)', '', $host);

        $parts = explode(".", $host);

        if ($domain !== self::DEFAULT_DOMAIN) {
            $firstPart = array_shift($parts);

            if ($firstPart === 'www') {
                $domain = $firstPart . '.' . $domain;
                $firstPart = array_shift($parts);
            }

            $domain = $domain . '.' . $firstPart;

            $host = $domain . '.' . implode(".", $parts);
        } else {
            $host = implode(".", $parts);
        }

        if ($customUri !== null) {
            $uri = $customUri;
        } else {
            $uri = $_SERVER['REQUEST_URI'];
        }

        $uri = parse_url(filter_var($uri, FILTER_SANITIZE_URL), PHP_URL_PATH);

        $link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . '://' . $host . $uri;

        return htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    }

    public static function getCities()
    {
        if (!Loader::includeModule(self::DEFAULT_MODULE_ID)) {
            return;
        }

        //! Тут берутся все города и в принципе данный блок статичный так как параметры урла не меняют запрос 
        return CityTable::getList([
            'select' => ['WF_CITY_NAME', 'WF_SUBDOMAIN'],
            'cache' => [
                'ttl' => 360000,
                'cache_joins' => true
            ],
        ])->fetchAll();
    }

    public static function getDomain()
    {
        if ($_SERVER["HTTPS"]) {
            $protocol = "https://";
        } else {
            $protocol = "http://";
        }

        $host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);

        $host = ltrim(str_replace($protocol, '', $host), 'www.');

        $host = preg_replace('(:+\d+)', '', $host);

        $parts = explode(".", $host);

        if (count($parts) === 2) {
            return self::DEFAULT_DOMAIN;
        }

        return $parts[0];
    }

    public static function getPage()
    {
        $host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);
        $uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);

        $uri = new Uri($host . $uri);
        $host = $uri->getHost();
        $path = $uri->getPath();

        return $host . $path;
    }

    public static function OnEndBufferContent(&$content)
    {
        return $content;

        $location = '';

        $path = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);

        $skipFlag = false;

        if ($path) {
            $location = trim($path);

            $parts = explode(DIRECTORY_SEPARATOR, $location);

            $allowedFolders = ['bitrix', 'local', 'upload', 'images'];

            if (isset($parts[1]) && (in_array($parts[1], $allowedFolders))) {
                $skipFlag = true;
            }
        } else {
            $skipFlag = true;
        }

        if (!$skipFlag) {
            if (!Loader::includeModule(self::DEFAULT_MODULE_ID)) {
                return;
            }
            //! SQL: 4 max on hit 
            //! SQL: 2 min on hit 

            // iblockIdCities определяет по поддомену города например: balashiha
            // кароч берет самый верхний уровень урла и ищет в инфоблоке городов
            // город по умолчанию это такая запись у которой поддомен пустой кароч
            // например есть город Москва
            // у нее поддомен: ""
            // а урл будет такой: https://seoarmor.ru/
            // а вот для Балашихи
            // поддомен: balashiha
            // а урл будет такой: https://balashiha.seoarmor.ru/
            $tableEntity = CityTable::getEntity();
            $tableFields = $tableEntity->getFields();
            $tablePK = $tableEntity->getPrimary();

            $arPropsAll = [];

            foreach ($tableFields as $field) {
                $fieldName = $field->getName();

                if ($fieldName === $tablePK) {
                    continue;
                }

                if ($fieldName === 'EXTERNAL_ID') {
                    continue;
                }

                $arPropsAll[] = $fieldName;
            }


            //! Тут берется всегда дефолтный город, кэш которого меняться не будет при изменении поддомена и параметров урла
            // SQL 1
            $arPropsDefault = CityTable::getList([
                'select' => ['*'],
                'filter' => ['=WF_SUBDOMAIN' => self::DEFAULT_DOMAIN],
                'cache' => [
                    'ttl' => 360000,
                    'cache_joins' => true
                ],
            ])->fetch();

            $domain = self::getDomain();

            if ($domain !== self::DEFAULT_DOMAIN) {
                //! Вот тут кэш отключен так как $domain - динамическая переменная, которая меняется в зависимости от запроса
                // SQL ~2
                $arProps = CityTable::getList([
                    'select' => ['*'],
                    'filter' => ['=WF_SUBDOMAIN' => $domain],
                ])->fetch();

                foreach ($arPropsAll as $propCode) {
                    $value = $arProps[$propCode];

                    if ($propCode === 'WF_PHONES' || $propCode === 'WF_EMAIL') {
                        if (is_array($value)) {
                            if (empty($value)) {
                                $value = $arPropsDefault[$propCode];
                            }
                        } else {
                            $value = $arPropsDefault[$propCode];
                        }

                        if (!is_array($value) || empty($value)) {
                            continue;
                        }

                        foreach ($value as $key => $_value) {
                            $content = str_replace('#' . $propCode . '_' . ($key + 1) . '#', htmlspecialchars_decode($_value), $content);
                        }
                    } else {
                        $replacement = '';

                        if (!is_array($value)) {
                            $replacement = $value;

                            if ($value == '' or empty($value)) {
                                $replacement = $arPropsDefault[$propCode];
                            }
                        } elseif (is_array($value)) {
                            if (count($value) == 1) {
                                $replacement = $value[0];

                                if ($value[0] == '' or empty($value[0])) {
                                    $replacement = $arPropsDefault[$propCode][0];
                                }
                            } else {
                                if (!empty($value)) {
                                    foreach ($value as $key => $_value) {
                                        $replacementItem = $_value;

                                        if ($_value == '' or empty($_value)) {
                                            $replacementItem = $arPropsDefault[$propCode][$key];
                                        }

                                        $replacement .= $replacementItem . ', ';
                                    }

                                    $replacement = rtrim($replacement, ', ');
                                }
                            }
                        }

                        if (is_array($replacement)) {
                            $replacement = implode(', ', $replacement);
                        }

                        $content = str_replace('#' . $propCode . '#', htmlspecialchars_decode($replacement), $content);
                    }
                }
            } else {
                foreach ($arPropsAll as $propCode) {
                    $value = $arPropsDefault[$propCode];

                    if ($propCode === 'WF_PHONES' || $propCode === 'WF_EMAIL') {
                        if (is_array($value)) {
                            foreach ($value as $key => $_value) {
                                $content = str_replace('#' . $propCode . '_' . ($key + 1) . '#', htmlspecialchars_decode($_value), $content);
                            }
                        }
                    } else {
                        $replacement = '';

                        if (!is_array($value)) {
                            $replacement = $value;
                        } elseif (is_array($value)) {
                            if (count($value) == 1) {
                                $replacement = $value[0];
                            } else {
                                if (!empty($value)) {
                                    foreach ($value as $key => $_value) {
                                        $replacementItem = $_value;
                                        $replacement .= $replacementItem . ', ';
                                    }

                                    $replacement = rtrim($replacement, ', ');
                                }
                            }
                        }

                        if (is_array($replacement)) {
                            $replacement = implode(', ', $replacement);
                        }

                        $content = str_replace('#' . $propCode . '#', htmlspecialchars_decode($replacement), $content);
                    }
                }
            }

            // iblockIdSeo кароч этот инфоблок нужен для уникальных тип внутренних страницы
            // ТУТ НУЖНО БРАТЬ ВСЮ URL текущей страницы без квери параметров http:// https:// www. и т.д.
            // и искать в этом инфоблоке по полю NAME
            // например: поиск по seoarmor.ru/uslugi/sozdanie-saytov/
            // а полный урл: https://ПОДДОМЕН_ЕСЛИ_ЕСТЬ.seoarmor.ru/uslugi/sozdanie-saytov/?utm_source=yandex&utm_medium=cpc
            $tableEntity = SeoTable::getEntity();
            $tableFields = $tableEntity->getFields();
            $tablePK = $tableEntity->getPrimary();

            $arPropsAll = [];

            foreach ($tableFields as $field) {
                $fieldName = $field->getName();

                if ($fieldName === $tablePK) {
                    continue;
                }

                if ($fieldName === 'EXTERNAL_ID') {
                    continue;
                }

                $arPropsAll[] = $fieldName;
            }

            //! Вот тут кэш отключен так как self::getPage() - динамическая переменная, которая меняется в зависимости от урла страницы
            // SQL ~3
            $arPropsDefault = SeoTable::getList([
                'select' => ['*'],
                'filter' => ['=WF_URL' => self::getPage()],
            ])->fetch();

            foreach ($arPropsAll as $propCode) {
                $value = $arPropsDefault[$propCode];

                if ($propCode === 'WF_SEO_TEXT') {
                    if (is_array($value)) {
                        foreach ($value as $key => $_value) {
                            $content = str_replace('#' . $propCode . '_' . ($key + 1) . '#', htmlspecialchars_decode($_value), $content);
                        }
                    }
                } else {
                    $replacement = '';

                    if (!is_array($value)) {
                        $replacement = $value;
                    } elseif (is_array($value)) {
                        if (count($value) == 1) {
                            $replacement = $value[0];
                        } else {
                            if (!empty($value)) {
                                foreach ($value as $key => $_value) {
                                    $replacementItem = $_value;
                                    $replacement .= $replacementItem . ', ';
                                }

                                $replacement = rtrim($replacement, ', ');
                            }
                        }
                    }

                    if (is_array($replacement)) {
                        $replacement = implode(', ', $replacement);
                    }

                    $content = str_replace('#' . $propCode . '#', htmlspecialchars_decode($replacement), $content);
                }
            }
        }
    }
}
