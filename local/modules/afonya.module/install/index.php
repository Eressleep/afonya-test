<?php

use Afonya\Module\Handler;
use Afonya\Module\LogTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;

class afonya_module extends CModule
{
    public function __construct()
    {
        if (file_exists(__DIR__ . '/version.php')) {
            $arModuleVersion = [];
            include_once(__DIR__ . '/version.php');

            if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
                $this->MODULE_VERSION = $arModuleVersion['VERSION'];
                $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            }

            $this->MODULE_ID = str_replace('_', '.', __CLASS__);

            $this->MODULE_NAME = Loc::getMessage('AFONYA_NAME');
            $this->MODULE_DESCRIPTION = Loc::getMessage('AFONYA_DESCRIPTION');

            $this->PARTNER_NAME = Loc::getMessage('AFONYA_PARTNER_NAME');
            $this->PARTNER_URI = Loc::getMessage('AFONYA_PARTNER_URI');

            $this->MODULE_SORT = 1;
            $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
            $this->MODULE_GROUP_RIGHTS = 'Y';
        }
    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws LoaderException
     * @throws SystemException
     */
    public function DoInstall(): bool
    {
        global $APPLICATION;

        if (CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            ModuleManager::registerModule($this->MODULE_ID);

            if (Loader::includeModule($this->MODULE_ID)) {
                $this->InstallDB();
                $this->InstallEvents();
                $this->InstallFiles();
                $this->InstallAgent();
            }
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

    /**
     * @throws LoaderException
     * @throws ArgumentException
     * @throws SystemException
     */
    public function InstallDB()
    {
        Loader::includeModule($this->MODULE_ID);
        if (!Application::getConnection()->isTableExists(
            Base::getInstance(LogTable::getEntity()->getDataClass())->getDBTableName()
        )) {
            LogTable::getEntity()->createDbTable();
        }
    }

    public function InstallEvents(): bool
    {
        EventManager::getInstance()->registerEventHandler(
            'iblock',
            'OnAfterIBlockElementAdd',
            $this->MODULE_ID,
            Handler::class,
            'create'
        );
        EventManager::getInstance()->registerEventHandler(
            'iblock',
            'OnAfterIBlockElementUpdate',
            $this->MODULE_ID,
            Handler::class,
            'change'
        );
        EventManager::getInstance()->registerEventHandler(
            'iblock',
            'OnAfterIBlockElementDelete',
            $this->MODULE_ID,
            Handler::class,
            'delete'
        );
        return false;
    }

    /**
     * @return bool
     */
    public function InstallAgent(): bool
    {

        CAgent::AddAgent(
            '\Afonya\Module\Agent::logSentNews();',
            'afonya.module',
            'Y',
            60 * 60 * 24 * 7,
            '',
            'Y',
            Handler::getCurrentTime()->add('7 day')->toString(),
            30,
        );
        CAgent::AddAgent(
            '\Afonya\Module\Agent::logSentUser();',
            'afonya.module',
            'Y',
            60 * 60 * 24 * 7,
            '',
            'Y',
            Handler::getCurrentTime()->add('7 day')->toString(),
            30,
        );

        return false;
    }

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws LoaderException
     * @throws SqlQueryException
     * @throws SystemException
     */
    public function DoUninstall(): bool
    {
        global $APPLICATION;

        if (Loader::includeModule($this->MODULE_ID)) {
            $this->UnInstallDB();
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->UnInstallAgent();
        }
        ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage('AFONYA_UNINSTALL_TITLE') . ' \'' . Loc::getMessage('AFONYA_NAME') . '\'',
            __DIR__ . '/unset.php'
        );

        return false;
    }

    public function UnInstallFiles(): bool
    {
        return false;
    }

    /**
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws SqlQueryException
     * @throws SystemException
     */
    public function UnInstallDB()
    {
        if (Application::getConnection()->isTableExists(
            Base::getInstance(LogTable::getEntity()->getDataClass())->getDBTableName()
        )) {
            $connection = Application::getInstance()->getConnection();
            $connection->dropTable(LogTable::getTableName());
        }

        Option::delete($this->MODULE_ID);
    }

    public function UnInstallEvents(): bool
    {
        EventManager::getInstance()->unRegisterEventHandler(
            'iblock',
            'OnAfterIBlockElementAdd',
            $this->MODULE_ID,
            Handler::class,
            'create'
        );
        EventManager::getInstance()->unRegisterEventHandler(
            'iblock',
            'OnAfterIBlockElementUpdate',
            $this->MODULE_ID,
            Handler::class,
            'change'
        );
        EventManager::getInstance()->unRegisterEventHandler(
            'iblock',
            'OnBeforeIBlockElementDelete',
            $this->MODULE_ID,
            Handler::class,
            'delete'
        );

        return false;
    }

    public function UnInstallAgent(): bool
    {
        CAgent::RemoveAgent('\Afonya\Module\Agent::logSentNews();', 'afonya.module');
        CAgent::RemoveAgent('\Afonya\Module\Agent::logSentUser();', 'afonya.module');

        return false;
    }
}
