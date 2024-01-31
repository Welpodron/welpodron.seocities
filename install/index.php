<?

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;

use Welpodron\SeoCities\CityTable;
use Welpodron\SeoCities\SeoTable;

//! TODO: Так как при установке модуля сприкты админки копируются в битрикс нужно понимать где находится папка с модулем
class welpodron_seocities extends CModule
{
    public function __construct()
    {
        $this->MODULE_ID = 'welpodron.seocities';
        $this->MODULE_VERSION = '1.0.0';
        $this->MODULE_NAME = 'Модуль для работы с SEO для поддоменов (welpodron.seocities)';
        $this->MODULE_DESCRIPTION = 'Модуль для работы с SEO для поддоменов';
        $this->PARTNER_NAME = 'Welpodron';
        $this->PARTNER_URI = 'https://github.com/Welpodron';
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler('main', 'OnEndBufferContent', $this->MODULE_ID, 'Welpodron\SeoCities\Utils', 'OnEndBufferContent');

        return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler('main', 'OnEndBufferContent', $this->MODULE_ID, 'Welpodron\SeoCities\Utils', 'OnEndBufferContent');
    }

    public function InstallFiles()
    {
        global $APPLICATION;

        try {
            if (!CopyDirFiles(__DIR__ . '/admin/', Application::getDocumentRoot() . '/bitrix/admin', true, true)) {
                $APPLICATION->ThrowException('Не удалось скопировать административные скрипты');
                return false;
            };
        } catch (\Throwable $th) {
            $APPLICATION->ThrowException($th->getMessage() . '\n' . $th->getTraceAsString());
            return false;
        }

        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(__DIR__ . '/admin/', Application::getDocumentRoot() . '/bitrix/admin');
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (!CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            $APPLICATION->ThrowException('Версия главного модуля ниже 14.00.00');
            return false;
        }

        if (!Loader::includeModule('welpodron.core')) {
            $APPLICATION->ThrowException('Модуль welpodron.core не был найден');
            return false;
        }

        if (!$this->InstallFiles()) {
            return false;
        }

        ModuleManager::registerModule($this->MODULE_ID);

        if (!$this->InstallDB()) {
            ModuleManager::unRegisterModule($this->MODULE_ID);

            return false;
        }

        if (!$this->InstallEvents()) {
            return false;
        }

        $APPLICATION->IncludeAdminFile('Установка модуля ' . $this->MODULE_ID, __DIR__ . '/step.php');
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $request = Context::getCurrent()->getRequest();

        if ($request->get("step") < 2) {
            $APPLICATION->IncludeAdminFile('Деинсталляция модуля ' . $this->MODULE_ID, __DIR__ . '/unstep1.php');
        } elseif ($request->get("step") == 2) {
            $this->UnInstallFiles();
            $this->UnInstallEvents();

            if ($request->get("savedata") != "Y") {
                $this->UnInstallDB();
            }

            ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile('Деинсталляция модуля ' . $this->MODULE_ID, __DIR__ . '/unstep2.php');
        }
    }

    public function InstallDB()
    {
        global $APPLICATION;

        if (!Loader::includeModule($this->MODULE_ID)) {
            $APPLICATION->ThrowException('Модуль ' . $this->MODULE_ID . ' не был найден');
            return false;
        }

        if (!Loader::includeModule('welpodron.core')) {
            $APPLICATION->ThrowException('Модуль welpodron.core не был найден');
            return false;
        }

        try {
            $connection = Application::getConnection();

            $entitySeo = SeoTable::getEntity();
            $entityCity = CityTable::getEntity();

            if (!$connection->isTableExists($entitySeo->getDBTableName())) {
                $entitySeo->createDBTable();
            }

            if (!$connection->isTableExists($entityCity->getDBTableName())) {
                $entityCity->createDBTable();
            }
        } catch (\Throwable $th) {
            $APPLICATION->ThrowException($th->getMessage() . '\n' . $th->getTraceAsString());
            return false;
        }

        return true;
    }

    public function UnInstallDb()
    {
        Loader::includeModule($this->MODULE_ID);
        Loader::includeModule('welpodron.core');

        $connection = Application::getConnection();

        if ($connection->isTableExists(SeoTable::getTableName())) {
            $connection->dropTable(SeoTable::getTableName());
        }

        if ($connection->isTableExists(CityTable::getTableName())) {
            $connection->dropTable(CityTable::getTableName());
        }
    }
}
