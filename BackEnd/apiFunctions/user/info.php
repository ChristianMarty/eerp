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
	
	$returnData['roles'] = array("inventory.print","inventory.create","purchasing.create","process.run","document.create","manufacturerPart.create","manufacturerPart.edit","supplier.view");
	$returnData['introduction'] = "I am Dev Mode";
	$returnData['avatar'] ="";
	$returnData['name'] = "DevMode";
	
	$returnData['rolesJson'] = json_decode( '{  "inventory": { "print": true,"create": true},"purchasing":{"create": true},"process":{"run": true},"document":{"create": true},"manufacturerPart":{"create": true,"edit": true}}');

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