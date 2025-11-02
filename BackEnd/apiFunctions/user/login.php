<?php
//*************************************************************************************************
// FileName : login.php
// FilePath : apiFunctions/user/
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

global $user;
global $api;

if($api->isPost())
{
	$data = $api->getPostData();
    if(!isset($data->UserName)) $api->returnData(\Error\parameterMissing("UserName"));
    if(!isset($data->Password)) $api->returnData(\Error\parameterMissing("Password"));

    $api->returnData($user->login($data->UserName, $data->Password));
}