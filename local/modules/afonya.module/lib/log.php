<?php

namespace Afonya\Module;


use Bitrix\Main\Entity;
use Bitrix\Main\Entity\DateField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

class LogTable extends Entity\DataManager
{

    public static function getTableName(): string
    {
        return 'log_news';
    }

    public static function getUfId(): string
    {
        return 'LOG_NEWS';
    }

    /**
     * @throws SystemException
     */
    public static function getMap(): array
    {
        return [
            new Entity\IntegerField(
                'ID',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                    'title'        => Loc::getMessage('AFONYA_LOG_TABLE_ID'),
                ]
            ),
            new Entity\BooleanField(
                'ADDING',
                [
                    'required' => true,
                    'title'    => Loc::getMessage('AFONYA_LOG_TABLE_ADDING'),
                ]
            ),
            new Entity\BooleanField(
                'CHANGING',
                [
                    'required' => true,
                    'title'    => Loc::getMessage('AFONYA_LOG_TABLE_CHANGING'),
                ]
            ),
            new Entity\BooleanField(
                'DELETING',
                [
                    'required' => true,
                    'title'    => Loc::getMessage('AFONYA_LOG_TABLE_DELETING'),
                ]
            ),
            new Entity\IntegerField(
                'NEWS_ID',
                [
                    'required' => true,
                    'title'    => Loc::getMessage('AFONYA_LOG_TABLE_NEWS_ID'),
                ]
            ),
            new Entity\IntegerField(
                'USER_ID',
                [
                    'required' => true,
                    'title'    => Loc::getMessage('AFONYA_LOG_TABLE_NEWS_ID'),
                ]
            ),
            new DateField(
                'PUBLISH_DATE',
                [
                    'required' => true,
                    'title'    => Loc::getMessage('AFONYA_LOG_TABLE_PUBLISH_DATE'),
                ]
            )
            // TODO: написать выражение для группировки
        ];
    }

}
