<?

namespace Welpodron\SeoCities\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Error;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Mail\Event as MailEvent;
use Bitrix\Main\Event as MainEvent;
use Bitrix\Main\UserConsent\Consent;
use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Query\QueryHelper;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Welpodron\SeoCities\CityTable;

class Receiver extends Controller
{
    const DEFAULT_MODULE_ID = 'welpodron.seocities';
    const DEFAULT_DOMAIN = 'DEFAULT';

    protected function getDefaultPreFilters()
    {
        return [];
    }

    public function exportWebflyIblockAction()
    {
        try {
            if (!Loader::includeModule('iblock')) {
                throw new \Exception('Модуль инфоблоков не найден');
            }

            $request = $this->getRequest();
            $arDataRaw = $request->getPostList()->toArray();

            if ($arDataRaw['sessid'] !== bitrix_sessid()) {
                throw new \Exception('Неверный идентификатор сессии');
            }

            if (!CurrentUser::get()->isAdmin()) {
                throw new \Exception('Нет прав на выполнение экспорта');
            }

            $iblockId = intval($arDataRaw['iblockId']);

            if ($iblockId <= 0) {
                throw new \Exception('Неверный идентификатор инфоблока');
            }

            $dbIblock = IblockTable::getList([
                'select' => ['ID', 'CODE'],
                'filter' => ['=ID' => $iblockId],
            ])->fetch();

            if (!$dbIblock) {
                return;
            }

            $iblockCode = $dbIblock['CODE'];

            if ($iblockCode === 'webfly_cities') {
                $nameAlias = 'WF_CITY_NAME';
            } elseif ($iblockCode === 'webfly_seo') {
                $nameAlias = 'WF_URL';
            } else {
                return;
            }

            $arFields = [];

            $dbElements =  ElementTable::getList([
                'select' => ['ID', $nameAlias => 'NAME', 'EXTERNAL_ID' => 'XML_ID'],
                'filter' => ['=IBLOCK_ID' => $iblockId],
            ])->fetchAll();

            foreach ($dbElements as $dbElement) {
                $dbProps = ElementPropertyTable::getList([
                    'select' => ['VALUE', 'MULTIPLE' => 'PROPERTY.MULTIPLE', 'CODE' => 'PROPERTY.CODE'],
                    'filter' => ['=IBLOCK_ELEMENT_ID' => $dbElement['ID']],
                    'runtime' => [
                        new Reference(
                            'PROPERTY',
                            PropertyTable::class,
                            Join::on('this.IBLOCK_PROPERTY_ID', 'ref.ID')
                        ),
                    ]
                ])->fetchAll();

                $arProps = [];

                foreach ($dbProps as $prop) {
                    $value = @unserialize($prop['VALUE']);

                    if ($value === false) {
                        $value = $prop['VALUE'];
                    } else {
                        if (is_array($value)) {
                            if (count($value) === 1) {
                                $value = $value[0];
                            } elseif ($value['TEXT']) {
                                $value = $value['TEXT'];
                            }
                        }
                    }

                    if ($value && $value !== '&nbsp;') {
                        if (is_string($value)) {
                            $value = trim($value);
                            $value = str_replace(["\r\n", "\r", "\n", "\t"], '', $value);
                        }

                        if ($prop['MULTIPLE'] == 'Y') {
                            $arProps[$prop['CODE']][] = $value;
                        } else {
                            $arProps[$prop['CODE']] = $value;
                        }
                    }
                }

                $arFields[] = array_merge($dbElement, $arProps);
            }

            // only if city iblock
            if ($iblockCode === 'webfly_cities') {
                foreach ($arFields as &$arField) {
                    if (!isset($arField['WF_SUBDOMAIN'])) {
                        $arField['WF_SUBDOMAIN'] = 'DEFAULT';
                    }
                }

                unset($arField);
            }

            return $arFields;
        } catch (\Throwable $th) {
            if (CurrentUser::get()->isAdmin()) {
                $this->addError(new Error($th->getMessage(), $th->getCode(), $th->getTrace()));
                return;
            } else {
                $this->addError(new Error('Произошла ошибка при обработке запроса'));
                return;
            }
        }
    }
}
