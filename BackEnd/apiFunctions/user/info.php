<?php
//*************************************************************************************************
// FileName : info.php
// FilePath : apiFunctions/user/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";
global $devMode;



$returnData = array();

if($devMode) // TODO: This is fundamentally broken -> fix it
{
	
	$json =  '{"inventory": { "print": true,"create": true},"purchasing":{"create": true},"process":{"run": true},"document":{"create": true},"manufacturerPart":{"create": true,"edit": true},"stock":{"create": true, "add": true, "remove":true, "count":true}}';
	
	$roles = json_decode($json);
	
	$roles_array = array();
	foreach($roles as $key => $category)
	{
		$categoryName = $key;
		foreach($category as $key => $role)
		{
			$roleStr = $categoryName.".".$key;
			if($role == true) array_push($roles_array, $roleStr);
		}
	}
	
	$returnData['roles'] = $roles_array;
	$returnData['introduction'] = "I am Dev Mode";
	$returnData['avatar'] ="";
	$returnData['name'] = "DevMode";
	
	$returnData['rolesJson'] = $roles;

	sendResponse($returnData);
}
else
{
	$returnData['roles'] = $_SESSION['UserRolesString'];
	$returnData['rolesJson'] = $_SESSION['UserRoles'];
	$returnData['introduction'] = "I am ".$_SESSION["username"];
	$returnData['avatar'] ="";
	$returnData['name'] = $_SESSION["username"];
	
	sendResponse($returnData);
}
?>