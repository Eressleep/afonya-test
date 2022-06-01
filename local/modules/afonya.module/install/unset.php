<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!check_bitrix_sessid()) {
    return;
}
echo(CAdminMessage::ShowNote(Loc::getMessage("AFONYA_UNSTEP_BEFORE") . " " . Loc::getMessage("AFONYA_UNSTEP_AFTER")));
?>

<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANG; ?>"/>
    <input type="submit" value="<?= Loc::getMessage("AFONYA_UNSTEP_SUBMIT_BACK") ?>">
</form>
