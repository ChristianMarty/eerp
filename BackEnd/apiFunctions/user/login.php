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
    $login = $user->login($data->username, $data->password);

    if(is_string($login)){
        $api->returnError($login);
    }

    $returnData = array();
    $returnData['DisplayName'] = $user->displayName();
    $returnData['UserRoles'] = $user->roles();

    $api->returnData($returnData);
}