<?php

namespace Welpodron\SeoCities;

// use Bitrix\Main\ORM\Query\QueryHelper;
// use Bitrix\Main\ORM\Query\Join;
// use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Loader;

use Welpodron\Core\ORM\Fields\HTMLTextFieldMultiple;

class SeoTable extends DataManager
{
    public static function getTableName()
    {
        return 'welpodron_seocities_seo';
    }

    public static function getMap()
    {
        if (!Loader::includeModule('welpodron.core')) {
            throw new Exception("Модуль welpodron.core не был найден");
        }

        return [
            new IntegerField('EXTERNAL_ID', [
                'title' => 'Внешний ID',
                'nullable' => true,
            ]),
            new IntegerField('ID', [
                'title' => 'ID',
                'primary' => true,
                'autocomplete' => true
            ]),
            new StringField('WF_URL', [
                'title' => 'URL страницы',
                'unique' => true,
                'required' => true,
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            $uri = new Uri($value);
                            $path = $uri->getPath();
                            $host = $uri->getHost();
                            return $host . $path;
                        }
                    ];
                }
            ]),
            new HTMLTextFieldMultiple('WF_SEO_TEXT', [
                'title' => 'SEO текст',
            ]),
            new StringField('WF_TITLE', [
                'title' => 'Заголовок страницы',
            ]),
            new TextField('WF_DESCRIPTION',  [
                'title' => 'Описание страницы',
            ]),
            new TextField('WF_KEYWORDS', [
                'title' => 'Ключевые слова',
            ]),
            new TextField('WF_ROBOTS', [
                'title' => 'Robots',
            ]),
        ];
    }
}
