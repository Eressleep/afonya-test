<?php

namespace Afonya\Module;

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Mail\Event;

class Agent{

    public static function logSent()
    {
        $status = Event::send([
            'EVENT_NAME' => 'AFONYA_LOG_1',
            'LID'        => 's1',
            'C_FIELDS'   => [
                'MESSAGE' => [
                    'test'  => 123,
                    'test1' => 123,
                ],
            ],
        ]);
        return '';
    }
}
