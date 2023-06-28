<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/vendor/contact
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET["VendorAddressId"]))
	{
		require_once __DIR__ . "/../_vendor.php";
		// legacy behavior
		$vendor = getVendorContact($_GET["VendorAddressId"]);
		sendResponse($vendor);
	}
	else if(isset($_GET["ContactId"]))
	{
		$dbLink = dbConnect();
		
		$contactId = dbEscapeString($dbLink, trim($_GET["ContactId"]));
		
		$query = "SELECT * FROM vendor_contact WHERE Id = {$contactId} ";
		
		$result = dbRunQuery($dbLink,$query);
		$data = dbGetResult($result);

		$output = array();
		$output['Id'] = intval($data['Id']);
		$output['VendorId'] = intval($data['VendorId']);
		$output['AddressId'] = intval($data['VendorAddressId']);
		$output['Gender'] = $data['Gender'];
        $output['JobTitle'] = $data['JobTitle'];
		$output['FirstName'] = $data['FirstName'];
		$output['LastName'] = $data['LastName'];
		$output['Language'] = $data['Language'];
		$output['Phone'] = $data['Phone'];
		$output['EMail'] = $data['E-Mail'];
		
		dbClose($dbLink);	
		sendResponse($output);
	}
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	if(!isset($data["VendorId"]))sendResponse(null, "VendorId not specified");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$insertData = array();
	$insertData['VendorId'] = intval($data['VendorId']);
	$insertData['VendorAddressId'] = intval($data['AddressId']);
	$insertData['Gender'] = $data['Gender'];
	$insertData['FirstName'] = $data['FirstName'];
	$insertData['LastName'] = $data['LastName'];
    $insertData['JobTitle'] = $data['JobTitle'];
	$insertData['Language'] = $data['Language'];
	$insertData['Phone'] = $data['Phone'];
	$insertData['E-Mail'] = $data['EMail'];
	
	$query = dbBuildInsertQuery($dbLink, "vendor_contact", $insertData);
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if(!$result)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse(null, $error);
}
else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	if(!isset($data["ContactId"]))sendResponse(null, "ContactId not specified");
	
	$dbLink = dbConnect();
	
	$contactId = intval($data["ContactId"]);
	
	$insertData = array();
	$insertData['VendorId'] = intval($data['VendorId']);
	$insertData['VendorAddressId'] = intval($data['AddressId']);
	$insertData['Gender'] = $data['Gender'];
	$insertData['FirstName'] = $data['FirstName'];
	$insertData['LastName'] = $data['LastName'];
    $insertData['JobTitle'] = $data['JobTitle'];
	$insertData['Language'] = $data['Language'];
	$insertData['Phone'] = $data['Phone'];
	$insertData['`E-Mail`'] = $data['EMail'];

	$query = dbBuildUpdateQuery($dbLink, "vendor_contact", $insertData, "Id = {$contactId}");
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if(!$result)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse(null, $error);
}

	
?>