<?php

namespace Afonya\Module;

use Bitrix\Main\Mail\Event;

class Agent
{

    public static function logSentNews()
    {
        $status = Event::send([
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

    public static function getLogNews(array $out = [])
    {
        $data = LogTable::getList([
            'filter' =>
                [
                    '!NEWS_ID'       => 1,
                    '>=PUBLISH_DATE' => Handler::getCurrentTime()->add('-7 day'),

                ],
        ])->fetchAll();

        foreach ($data as $item) {
            $out[$item['NEWS_ID']] = [
                'ADDING'   => ($out[$item['NEWS_ID']]['ADDING'] + $item['ADDING']) > 0 ? 1 : 0,
                'CHANGING' => ($out[$item['NEWS_ID']]['CHANGING'] + $item['CHANGING']) > 0 ? 1 : 0,
                'DELETING' => ($out[$item['NEWS_ID']]['DELETING'] + $item['DELETING']) > 0 ? 1 : 0,
            ];
        }
        return $out;
    }

    public static function logSentUser()
    {
        $status = Event::send([
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

    public static function getLogUsers(array $out = [])
    {
        $data = LogTable::getList([
            'filter' => [
                '>=PUBLISH_DATE' => Handler::getCurrentTime()->add('-7 day'),
                '!NEWS_ID'       => 1,
            ],
        ])->fetchAll();
        $out = [];
        foreach ($data as $item) {
            $out[$item['USER_ID']]['ACTION'] += (bool)$item['ADDING'] + (bool)$item['CHANGING'] + (bool)$item['DELETING'];
        }
        return $out;
    }
}
