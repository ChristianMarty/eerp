<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/vendor/alias
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["AliasId"]))sendResponse(null, "AliasId not specified");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$aliasId = dbEscapeString($dbLink, trim($_GET["AliasId"]));
	
	$query = "SELECT * FROM vendor_alias WHERE Id = {$aliasId} ";
	
	$result = dbRunQuery($dbLink,$query);
	$data = dbGetResult($result);

	$output = array();
	$output['Id'] = intval($data['Id']);
	$output['VendorId'] = intval($data['VendorId']);
	$output['Name'] = $data['Name'];
	$output['Note'] = $data['Note'];
	
	dbClose($dbLink);	
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	if(!isset($data["VendorId"]))sendResponse(null, "VendorId not specified");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$inserData = array();
	$inserData['VendorId']= intval($data['VendorId']);
	$inserData['Name']  = dbEscapeString($dbLink,trim($data['Name']));
	$inserData['Note']  = dbEscapeString($dbLink,trim($data['Note']));
	
	$query = dbBuildInsertQuery($dbLink, "vendor_alias", $inserData);
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if($result == false)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse(null, $error);
	
}
else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	if(!isset($data["AliasId"]))sendResponse(null, "AliasId not specified");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$aliasId = intval($data["AliasId"]);
	
	$insertData = array();
	$insertData['Name']  = dbEscapeString($dbLink,trim($data['Name']));
	$insertData['Note']  = dbEscapeString($dbLink,trim($data['Note']));
	
	$query = dbBuildUpdateQuery($dbLink, "vendor_alias", $insertData, "Id = {$aliasId}");
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if($result == false)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse(null, $error);
}

	
?>