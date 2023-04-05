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

function buildRoles($rolesObject, &$roleStringArray, $roleStringPart)
{
	$categoryStringPart = $roleStringPart;
	
	foreach($rolesObject as $key => $role)
	{
		if(is_object($role))
		{
			$roleStringPart = buildRoles($role,$roleStringArray,$categoryStringPart.$key.".");
		}
		else
		{
			if($role) $roleStringArray[] = $roleStringPart . $key;
		}
	}

	return $categoryStringPart;
}

$returnData = array();

if($devMode) // TODO: This is fundamentally broken -> fix it
{
$json =  '{ "assembly":{"view": true, "create": true, "unit" : {"add": true, "history" : {"add": true, "edit": true}}},
			"inventory":{"print": true,"create": true, "history" : {"add": true, "edit": true}, "accessory": {"add": true, "edit": true}, "purchase": {"edit": true}},
			"metrology":{"view": true, "create": true},
			"purchasing":{"create": true, "edit": true, "confirm": true},
			"vendor":{"view": true, "create": true, "edit": true},
			"supplierPart":{"create": true},
			"process":{"run": true},
			"document":{"upload": true, "create": true, "ingest": true},
			"manufacturerPart":{"create": true,"edit": true},
			"stock":{"create": true, "add": true, "remove":true, "count":true, "delete":true}, 
			"location":{"transfer":true, "bulkTransfer":true, "print": true},
			"finance":{"view":true},
			"bom":{"print":true},
            "workOrder":{"create": true, "edit": true}}';
			
	$settingsJson = '{	"Default": {"StockLabelPrinter": 1,"StockLabel": 1,"BomPrinter": 2,"AssemblyReportPrinter": 2, "AssemblyReportTemplate": 3, "PartReceiptPrinter":2, "PurchasOrder": {"UoM": 29, "VAT": 1}}}';
	
	$settings = json_decode($settingsJson);
	$roles = json_decode($json);
	
	$roles_array = array();
	buildRoles($roles, $roles_array, "");
	
	$returnData['roles'] = $roles_array;
	$returnData['introduction'] = "I am in Dev Mode";
	$returnData['avatar'] ="";
	$returnData['name'] = "DevMode";
	
	$returnData['rolesJson'] = $roles;
	$returnData['settings'] = $settings;

	sendResponse($returnData);
}
else
{
	$roles_array = array();
	buildRoles($_SESSION['UserRoles'], $roles_array, "");
	
	$returnData['roles'] = $roles_array;
	$returnData['settings'] = $_SESSION["Settings"];
	$returnData['rolesJson'] = $_SESSION['UserRoles'];
	$returnData['introduction'] = "I am ".$_SESSION["username"];
	$returnData['avatar'] ="";
	$returnData['name'] = $_SESSION["username"];
	
	sendResponse($returnData);
}
?>