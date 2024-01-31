<?php

namespace Welpodron\SeoCities;

use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\BooleanField;

use Bitrix\Iblock\IblockTable;

use Bitrix\Main\ORM\Data\DataManager;

use Bitrix\Main\Loader;

use Welpodron\Core\ORM\Fields\HTMLTextField;
use Welpodron\Core\ORM\Fields\TextFieldMultiple;

class CityTable extends DataManager
{
    public static function getTableName()
    {
        return 'welpodron_seocities_city';
    }

    public static function getMap()
    {
        if (!Loader::includeModule('welpodron.core')) {
            throw new Exception("Модуль welpodron.core не был найден");
        }
        // $externalField = new IntegerField('EXTERNAL_ID', [
        //     'title' => 'Внешний ID',
        // ]);

        // $externalField->setParameter('hidden', true);

        return [
            //! generic table fields start
            // $externalField,
            new IntegerField('EXTERNAL_ID', [
                'title' => 'Внешний ID',
                'nullable' => true,
            ]),
            // new BooleanField('ACTIVE', [
            //     'title' => 'Активность элемента',
            // ]),
            new IntegerField('ID', [
                'title' => 'ID',
                'primary' => true,
                'autocomplete' => true
            ]),
            // 'MODIFIED_BY' => new ORM\Fields\IntegerField('MODIFIED_BY', array(
            // 	'title' => 'Изменено пользователем',
            // )),
            // 'MODIFIED_BY_USER' => new ORM\Fields\Relations\Reference(
            // 	'MODIFIED_BY_USER',
            // 	'\Bitrix\Main\User',
            // 	array('=this.MODIFIED_BY' => 'ref.ID'),
            // 	array('join_type' => 'LEFT')
            // ),
            // 'CREATED_BY_USER' => new ORM\Fields\Relations\Reference(
            // 	'CREATED_BY_USER',
            // 	'\Bitrix\Main\User',
            // 	array('=this.CREATED_BY' => 'ref.ID'),
            // 	array('join_type' => 'LEFT')
            // ),
            //! generic table fields end 
            new StringField('WF_CITY_NAME', [
                'title' => 'Название города в именительном падеже',
                'unique' => true,
                'required' => true,
            ]),
            new StringField('WF_CITY_ROD', [
                'title' => 'Название города в родительном падеже',
            ]),
            new StringField('WF_CITY_TVOR', [
                'title' => 'Название города в творительном падеже',
            ]),
            new StringField('WF_CITY_VIN', [
                'title' => 'Название города в винительном падеже',
            ]),
            new StringField('WF_CITY_DAT', [
                'title' => 'Название города в дательном падеже',
            ]),
            new StringField('WF_CITY_PRED', [
                'title' => 'Название города в предложном падеже',
            ]),
            new StringField('WF_SUBDOMAIN', [
                'title' => 'Поддомен',
                'unique' => true,
                'required' => true,
            ]),
            new TextFieldMultiple('WF_PHONES', [
                'title' => 'Телефон(ы)',
            ]),
            new TextFieldMultiple('WF_EMAIL', [
                'title' => 'E-mail(ы)',
            ]),
            new HTMLTextField('WF_CONTACTS', [
                'title' => 'Контактная информация',
            ]),
            new HTMLTextField('WF_META', [
                'title' => 'Метатег для webmaster.yandex',
            ]),
            new HTMLTextField('WF_COUNT', [
                'title' => 'Область для счетчиков',
            ]),
            new HTMLTextField('WF_MAP', [
                'title' => 'Код карты',
            ]),
        ];
    }
}
