<?php
//*************************************************************************************************
// FileName : info.php
// FilePath : apiFunctions/user/
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

global $user;
global $api;

if($api->isGet())
{
    $api->returnData($user->info());
}
