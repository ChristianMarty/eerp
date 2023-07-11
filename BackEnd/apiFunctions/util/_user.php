<?php
//*************************************************************************************************
// FileName : _user.php
// FilePath : apiFunctions/util/
// Author   : Christian Marty
// Date		: 12.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";

function user_getId(): int|null
{
    global $devMode;
    if($devMode) return null;
    else return $_SESSION["userid"];
}

function user_getVatIdDefault(): int
{
    global $devMode;
    if($devMode) return 1;
    else return 1; // Todo: load default from DB
}
?>