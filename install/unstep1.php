<?

use \Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
    return;

if ($ex = $APPLICATION->GetException())
    echo CAdminMessage::ShowMessage(array(
        "TYPE" => "ERROR",
        "MESSAGE" => Loc::getMessage("MOD_UNINST_ERR"),
        "DETAILS" => $ex->GetString(),
        "HTML" => true,
    ));

?>
<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="hidden" name="id" value="welpodron.seocities">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?= CAdminMessage::ShowMessage(Loc::getMessage("MOD_UNINST_WARN")) ?>
    <p><?= Loc::getMessage("MOD_UNINST_SAVE") ?></p>
    <div class="adm-info-message">
        <b style="color:red;">Если вы не уверены в том, что делаете, то оставьте опцию включенной.</b>
    </div>
    <p>
        <input type="checkbox" name="savedata" id="savedata" value="Y" checked>
        <label for="savedata">
            <?= Loc::getMessage("MOD_UNINST_SAVE_TABLES") ?>
        </label>
    </p>
    <input type="submit" name="" value="<?= Loc::getMessage("MOD_UNINST_DEL") ?>">
    <form>