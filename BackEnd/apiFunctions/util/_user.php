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

function user_getId(): int
{
    global $devMode;
    if($devMode)
    {
        return 1;
    }
    return $_SESSION["userid"];
}
?>