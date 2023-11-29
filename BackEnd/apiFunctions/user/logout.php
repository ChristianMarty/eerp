<?php
//*************************************************************************************************
// FileName : logout.php
// FilePath : apiFunctions/user/
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

global $user;
global $api;
$user->logout();
$api->returnEmpty();
