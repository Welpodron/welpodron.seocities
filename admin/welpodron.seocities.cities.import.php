<?php

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */

use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/prolog.php");
// define("HELP_FILE", "settings/agreement_edit.php");

Loc::loadMessages(__FILE__);

$canEdit = $USER->CanDoOperation('edit_other_settings');
$canView = $USER->CanDoOperation('view_other_settings');
if (!$canEdit && !$canView) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/prolog_admin_after.php");

global $adminSidePanelHelper;

$componentParameters = [
    'ID' => $_REQUEST['ID'] ?? '',
    'MODULE_ID' => 'welpodron.seocities',
    'TABLE_CLASS' => 'Welpodron\SeoCities\CityTable',
    'PATH_TO_LIST' => BX_ROOT . '/admin/welpodron.seocities.cities.items.php?lang=' . LANGUAGE_ID,
    'CAN_EDIT' => $canEdit
];

if ($adminSidePanelHelper->isSidePanel()) {
    $APPLICATION->IncludeComponent(
        'bitrix:ui.sidepanel.wrapper',
        '',
        [
            'POPUP_COMPONENT_NAME' => 'welpodron:admin.ui.grid.import',
            'POPUP_COMPONENT_TEMPLATE_NAME' => '',
            'POPUP_COMPONENT_PARAMS' => $componentParameters,
            'RELOAD_GRID_AFTER_SAVE' => true,
        ]
    );
} else {
    LocalRedirect($componentParameters['PATH_TO_LIST']);
}

require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php");
