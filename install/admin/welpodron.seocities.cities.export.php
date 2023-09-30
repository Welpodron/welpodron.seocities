<?
if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/local' . '/modules/' . 'welpodron.seocities' . '/admin/' . 'welpodron.seocities.cities.export.php')) {
    require_once($_SERVER["DOCUMENT_ROOT"] . '/local' . '/modules/' . 'welpodron.seocities' . '/admin/' . 'welpodron.seocities.cities.export.php');
} else {
    require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . '/modules/' . 'welpodron.seocities' . '/admin/' . 'welpodron.seocities.cities.export.php');
}
