<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
    return;
}


if ($errorException = $APPLICATION->GetException()) {
    echoCAdminMessage::ShowMessage([
        'TYPE'     => 'ERROE',
        'MESSAGE'  => '',
        'DETAILES' => $errorException->GetString(),
        'HTML'     => true,
    ]);
} else {
    echo(CAdminMessage::ShowNote(Loc::getMessage('AFONYA_STEP_BEFORE') . ' ' . Loc::getMessage('AFONYA_STEP_AFTER')));
}
?>

<form action='<?= $APPLICATION->GetCurPage(); ?>'>
    <input type='hidden' name='lang' value='<?= LANG; ?>'/>
    <input type='submit' value='<?= Loc::getMessage('AFONYA_STEP_SUBMIT_BACK'); ?>'>
</form>
