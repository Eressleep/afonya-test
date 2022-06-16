<?php

namespace Afonya\Module;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UserTable;
use CAgent;

class Agent
{

    /**
     * @return bool
     * @throws ObjectException
     */
    public static function logSentNews(): bool
    {
        self::updateAgent('news_data_send', 'afonya.module::logSentNews();');
        Event::send([
            'EVENT_NAME' => 'AFONYA_LOG_NEWS',
            'LID'        => 's1',
            'C_FIELDS'   => [
                'MESSAGE' => [
                    self::getLogNews(),
                ],
            ],
        ]);
        try {
            Option::set('afonya.module', 'news_data_send', Handler::getCurrentTime(), false);
        } catch (ArgumentOutOfRangeException|ObjectException $e) {
        }
        return true;
    }

    /**
     * @param array $answer
     *
     * @return string
     */
    public static function getLogNews(array $answer = []): string
    {
        try {
            $data = LogTable::getList([
                'select' => [
                    '*',
                ],
                'filter' =>
                    [
                        '!NEWS_ID'       => 1,
                        '>=PUBLISH_DATE' => Handler::getCurrentTime()->add('-7 day'),

                    ],
            ])->fetchAll();
            $out = [];
            foreach ($data as $item) {
                $out[$item['NEWS_ID']] = [
                    'ADDING'   => ($out[$item['NEWS_ID']]['ADDING'] + $item['ADDING']) > 0 ? 1 : 0,
                    'CHANGING' => ($out[$item['NEWS_ID']]['CHANGING'] + $item['CHANGING']) > 0 ? 1 : 0,
                    'DELETING' => ($out[$item['NEWS_ID']]['DELETING'] + $item['DELETING']) > 0 ? 1 : 0,
                ];
            }
            $answer[] = "Общее количество измененный новостей " . count($out) . ".";
            foreach ($out as $key => $value) {
                $answer[] = "Новость {$key}. добавлено: {$value['ADDING']}, отредактировано: {$value['CHANGING']},удалено: {$value['DELETING']}.";
            }
        } catch (ObjectPropertyException|ArgumentException|ObjectException|SystemException $e) {
        }
        return implode('<br>', $answer);
    }


    /**
     * @throws ObjectException
     */
    public static function updateAgent(string $name, string $funcName)
    {
        $lastDate = Option::get('afonya.module', $name);
        if (strlen($lastDate) == 0) {
            $lastDate = Handler::getCurrentTime()->toString();
        }
        $data = CAgent::GetList([], ['NAME' => $funcName])->Fetch();
        try {
            CAgent::Update(
                $data['ID'],
                ['NEXT_EXEC' => Handler::getCurrentTime(strtotime($lastDate))->add('7 day')->toString()]
            );
        } catch (ObjectException $e) {
        }
    }

    /**
     * @return bool
     * @throws ObjectException
     */
    public static function logSentUser(): bool
    {
        self::updateAgent('users_data_send', 'afonya.module::logSentUser();');
        Event::send([
            'EVENT_NAME' => 'AFONYA_LOG_USER',
            'LID'        => 's1',
            'C_FIELDS'   => [
                'MESSAGE' => [
                    self::getLogUsers(),
                ],
            ],
        ]);
        try {
            Option::set('afonya.module', 'users_data_send', Handler::getCurrentTime(), false);
        } catch (ArgumentOutOfRangeException|ObjectException $e) {
        }
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
            ],
            )->fetchAll();
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
