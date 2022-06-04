<?php

namespace Afonya\Module;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;

class Agent
{

    public static function logSentNews(): bool
    {
        Event::send([
            'EVENT_NAME' => 'AFONYA_LOG_NEWS',
            'LID'        => 's1',
            'C_FIELDS'   => [
                'MESSAGE' => [
                    self::getLogNews(),
                ],
            ],
        ]);
        return true;
    }

    /**
     * @param array $data
     *
     * @return int
     */
    public static function getLogNews(array $data = []): int
    {
        try {
            $data = LogTable::getList([
                'select' => [
                    'NEWS_ID',
                ],
                'filter' =>
                    [
                        '!NEWS_ID'       => 1,
                        '>=PUBLISH_DATE' => Handler::getCurrentTime()->add('-7 day'),

                    ],
                'group'  => ['NEWS_ID'],
            ])->fetchAll();
        } catch (ObjectPropertyException|ArgumentException|ObjectException|SystemException $e) {
        }
        return count($data);
    }

    public static function logSentUser(): bool
    {
        Event::send([
            'EVENT_NAME' => 'AFONYA_LOG_USER',
            'LID'        => 's1',
            'C_FIELDS'   => [
                'MESSAGE' => [
                    self::getLogUsers(),
                ],
            ],
        ]);
        return true;
    }

    /**
     * @param array $out
     * @param array $data
     *
     * @return array
     */
    public static function getLogUsers(array $out = [], array $data = []): ?array
    {
        try {
            $data = LogTable::getList([
                'select'  => [
                    'USER_ID',
                    'ADDING',
                    'CHANGING',
                    'DELETING',
                    'NAME_'        => 'USER.NAME',
                    'SECOND_NAME_' => 'USER.SECOND_NAME',
                    'LAST_NAME_'   => 'USER.LAST_NAME',
                ],
                'filter'  => [
                    '!NEWS_ID'       => 1,
                    '>=PUBLISH_DATE' => Handler::getCurrentTime()->add('-7 day'),
                ],
                // TODO: перенести в log в Entity\ExpressionField
                'runtime' => [
                    'USER' => [
                        'data_type' => UserTable::class,
                        'reference' => ['=this.USER_ID' => 'ref.ID'],
                    ],
                ],
            ],)->fetchAll();
        } catch (ObjectPropertyException|SystemException|ObjectException $e) {
        }
        foreach ($data as $item) {
            $out[$item['USER_ID']]['ACTION'] += (bool)$item['ADDING'] + (bool)$item['CHANGING'] + (bool)$item['DELETING'];
            $out[$item['USER_ID']]['FIO'] = implode(' ', [$item['NAME_'], $item['SECOND_NAME_'], $item['LAST_NAME_']]
            );
        }
        return max($out);
    }
}
