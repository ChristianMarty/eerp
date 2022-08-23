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
	
$json =  '{"assembly":{"view": true, "create": true, "history" : {"add": true, "edit": true}},"inventory": { "print": true,"create": true},"purchasing":{"create": true, "edit": true, "confirm": true},"supplier":{"view": true, "create": true},"supplierPart":{"create": true},"process":{"run": true},"document":{"create": true},"manufacturerPart":{"create": true,"edit": true},"stock":{"create": true, "add": true, "remove":true, "count":true}, "location":{"transfer":true, "bulkTransfer":true, "print": true}}';
	$settingsJson = '{	"Default": {"StockLabelPrinter": 1,"StockLabel": 1,"BomPrinter": 2,"AssemblyReportPrinter": 2, "AssemblyReportTemplate": 3, "PurchasOrder": {"UoM": 29, "VAT": 1}}}';
	
	$settings = json_decode($settingsJson);
	$roles = json_decode($json);
	
	$roles_array = array();
	foreach($roles as $key => $category)
	{
		$categoryName = $key;
		foreach($category as $key => $role)
		{
			if(is_object($role))
			{
				$subCategoryName = $key;
				foreach($role as $key => $role)
				{
					$roleStr = $categoryName.".".$subCategoryName.".".$key;
					if($role == true) array_push($roles_array, $roleStr);
				}
			}
			else
			{
				$roleStr = $categoryName.".".$key;
				if($role == true) array_push($roles_array, $roleStr);
			}
		}
	}
	
	$returnData['roles'] = $roles_array;
	$returnData['introduction'] = "I am Dev Mode";
	$returnData['avatar'] ="";
	$returnData['name'] = "DevMode";
	
	$returnData['rolesJson'] = $roles;
	$returnData['settings'] = $settings;

	sendResponse($returnData);
}
else
{
	$returnData['roles'] = $_SESSION['UserRolesString'];
	$returnData['settings'] = $_SESSION["Settings"];
	$returnData['rolesJson'] = $_SESSION['UserRoles'];
	$returnData['introduction'] = "I am ".$_SESSION["username"];
	$returnData['avatar'] ="";
	$returnData['name'] = $_SESSION["username"];
	
	sendResponse($returnData);
}
?>