<?php

namespace Afonya\Module;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type;
use Bitrix\Main\Type\DateTime;
use Exception;


class Handler
{

    private static $newsIblock = 1;


    /**
     * @throws ObjectException
     */
    public static function getCurrentTime(): Type\Date
    {
        return new Type\Date(
            DateTime::createFromTimestamp(time()),
            DateTime::getFormat()
        );
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    private static function checkAvailability(string $newsId): array
    {
        return
            LogTable::getList([
                'filter' => [
                    'NEWS_ID' => $newsId,
                    'USER_ID' => CurrentUser::get()->getId(),
                ],
            ])->fetchAll();
    }

    /**
     * @throws ArgumentException
     * @throws ObjectException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function create($arFields)
    {
        if (self::$newsIblock == $arFields['IBLOCK_ID'] and count(self::checkAvailability($arFields['RESULT'])) == 0) {
            try {
                LogTable::add([
                    'ADDING'       => 1,
                    'CHANGING'     => 0,
                    'DELETING'     => 0,
                    'NEWS_ID'      => $arFields['RESULT'],
                    'USER_ID'      => CurrentUser::get()->getId(),
                    'PUBLISH_DATE' => self::getCurrentTime(),
                ]);
            } catch (ObjectException|Exception $e) {
            }
        }
    }

    /**
     * @param $arFields
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function change($arFields)
    {
        if (self::$newsIblock == $arFields['IBLOCK_ID']) {
            $news = self::checkAvailability($arFields['ID'])[0];
            try {
                if ($news['USER_ID'] == CurrentUser::get()->getId()) {
                    LogTable::update(
                        $news['ID'],
                        [
                            'CHANGING' => ++$news['CHANGING'],
                        ]
                    );
                } else {
                    if (count(self::checkAvailability($arFields['ID'])) == 0) {
                        LogTable::add([
                            'ADDING'       => 0,
                            'CHANGING'     => 1,
                            'DELETING'     => 0,
                            'NEWS_ID'      => $arFields['ID'],
                            'USER_ID'      => CurrentUser::get()->getId(),
                            'PUBLISH_DATE' => self::getCurrentTime(),
                        ]);
                    }
                }
            } catch (Exception $e) {
            }
        }
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     * @throws Exception
     */
    public function delete($arFields)
    {
        if (self::$newsIblock == $arFields['IBLOCK_ID']) {
            $news = self::checkAvailability($arFields['ID'])[0];
            LogTable::update(
                $news['ID'],
                [
                    'DELETING' => 1,
                ]
            );
        }
    }
}
