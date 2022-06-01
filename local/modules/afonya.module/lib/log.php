<?php

namespace Afonya\Module;


use Bitrix\Main\Entity;


class LogTable extends Entity\DataManager
{

    public static function getTableName()
    {
        return 'log_news';
    }

    public static function getUfId()
    {
        return 'LOG_NEWS';
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField(
                'ID',
                [
                    'primary'      => true,
                    'autocomplete' => true,
                ]
            ),
            new Entity\IntegerField(
                'ADDING',
                [
                    'required' => true,
                ]
            ),
            new Entity\IntegerField(
                'CHANGING',
                [
                    'required' => true,
                ]
            ),
            new Entity\IntegerField(
                'DELETING',
                [
                    'required' => true,
                ]
            ),
            new Entity\IntegerField(
                'NEWS_ID',
                [
                    'required' => true,
                ]
            ),
            new Entity\IntegerField(
                'USER_ID',
                [
                    'required' => true,
                ]
            ),
            new Entity\DateField('PUBLISH_DATE')
            // TODO: написать выражение для группировки
        ];
    }

}
