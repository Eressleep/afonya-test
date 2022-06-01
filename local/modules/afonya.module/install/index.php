<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class afonya_module extends CModule
{
    public function __construct()
    {
        if (file_exists(__DIR__ . '/version.php')) {
            $arModuleVersion = [];
            include_once(__DIR__ . '/version.php');

            $this->MODULE_ID = str_replace('_', '.', get_class($this));
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->MODULE_NAME = Loc::getMessage('AFONYA_NAME');
            $this->MODULE_DESCRIPTION = Loc::getMessage('AFONYA_DESCRIPTION');
            $this->PARTNER_NAME = Loc::getMessage('AFONYA_PARTNER_NAME');
            $this->PARTNER_URI = Loc::getMessage('AFONYA_PARTNER_URI');
        }
    }

    /**
     * @return bool
     */
    public function DoInstall(): bool
    {
        global $APPLICATION;

        if (CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            $this->InstallFiles();
            $this->InstallDB();

            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallEvents();
        } else {
            $APPLICATION->ThrowException(
                Loc::getMessage('AFONYA_INSTALL_ERROR_VERSION')
            );
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('AFONYA_INSTALL_TITLE') . ' \'' . Loc::getMessage('AFONYA_NAME') . '\'',
            __DIR__ . '/step.php'
        );

        return false;
    }

    public function InstallFiles(): bool
    {
        return false;
    }

    public function InstallDB(): bool
    {
        return false;
    }

    public function InstallEvents(): bool
    {
        EventManager::getInstance()->registerEventHandler(
            'main',
            'OnBeforeEndBufferContent',
            $this->MODULE_ID,
            'afonya\ToTop\Main',
            'appendScriptsToPage'
        );
        return false;
    }

    public function DoUninstall(): bool
    {
        global $APPLICATION;

        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('AFONYA_UNINSTALL_TITLE') . ' \'' . Loc::getMessage('AFONYA_NAME') . '\'',
            __DIR__ . '/unstep.php'
        );

        return false;
    }

    public function UnInstallFiles(): bool
    {
        Directory::deleteDirectory(
            Application::getDocumentRoot() . '/bitrix/js/' . $this->MODULE_ID
        );

        Directory::deleteDirectory(
            Application::getDocumentRoot() . '/bitrix/css/' . $this->MODULE_ID
        );

        return false;
    }

    public function UnInstallDB(): bool
    {
        Option::delete($this->MODULE_ID);

        return false;
    }

    public function UnInstallEvents(): bool
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'main',
            'OnBeforeEndBufferContent',
            $this->MODULE_ID,
            'AFONYA\ToTop\Main',
            'appendScriptsToPage'
        );

        return false;
    }
}
