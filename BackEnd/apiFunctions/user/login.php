<?php
//*************************************************************************************************
// FileName : login.php
// FilePath : apiFunctions/user/
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

global $user;
global $api;

if($api->isPost())
{
	$data = $api->getPostData();
    $user->login($data->username,$data->password);

    if(!$user->loggedIn()) $api->returnError("Username or Password Wrong");

    $returnData = array();
    $returnData['DisplayName'] = $user->displayName();
    $returnData['UserRoles'] = $user->roles();

    $api->returnData($returnData);
}