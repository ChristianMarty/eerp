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

// TODO: This is fundamentally broken -> fix it

$returnData = array();

if($devMode)
{
	$returnData['roles'] = array("inventory.print","inventory.create","purchasing.create","process","document.create");
	$returnData['introduction'] = "I am Dev Mode";
	$returnData['avatar'] ="";
	$returnData['name'] = "DevMode";
	
	sendResponse($returnData);
}

if($_SESSION["username"] == "admin")
{	
	$returnData['roles'] = array("inventory.print","inventory.create","purchasing.create","process","document.create");
	$returnData['introduction'] = "I am a super administrator";
	$returnData['avatar'] ="";
	$returnData['name'] ="Admin";
	
}
else
{
	$returnData['roles'] = array("inventory.print","inventory.create","purchasing.create","document.create");//$_SESSION['roles'];
	$returnData['introduction'] = "I am ".$_SESSION["username"];
	$returnData['avatar'] ="";
	$returnData['name'] = $_SESSION["username"];
}

sendResponse($returnData);

?>