<?php

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/prolog.php");
define("ADMIN_MODULE_NAME", "welpodron.seocities");
// define("HELP_FILE", "settings/agreement_admin.php");

Loc::loadMessages(__FILE__);

$canEdit = $USER->CanDoOperation('edit_other_settings');
$canView = $USER->CanDoOperation('view_other_settings');
if (!$canEdit && !$canView) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

global $adminPage;
$adminPage->hideTitle();

require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/prolog_admin_after.php");


$APPLICATION->IncludeComponent(
    "welpodron:admin.ui.grid.items",
    "",
    array(
        'MODULE_ID' => 'welpodron.seocities',
        'TABLE_CLASS' => 'Welpodron\SeoCities\CityTable',
        'PATH_TO_LIST' => BX_ROOT . '/admin/welpodron.seocities.cities.items.php?lang=' . LANGUAGE_ID,
        'PATH_TO_ADD' => BX_ROOT . '/admin/welpodron.seocities.cities.item.php?ID=0&lang=' . LANGUAGE_ID,
        'PATH_TO_EDIT' => BX_ROOT . '/admin/welpodron.seocities.cities.item.php?ID=#id#&lang=' . LANGUAGE_ID,
        'PATH_TO_EXPORT' => BX_ROOT . '/admin/welpodron.seocities.cities.export.php?ID=#id#&lang=' . LANGUAGE_ID,
        'PATH_TO_IMPORT' => BX_ROOT . '/admin/welpodron.seocities.cities.import.php?lang=' . LANGUAGE_ID,
        'CAN_EDIT' => $canEdit,
        'ADMIN_MODE' => true
    )
);

require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
