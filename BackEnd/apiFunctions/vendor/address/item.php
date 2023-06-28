<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/vendor/address
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["AddressId"]))sendResponse(null, "AddressId not specified");
	
	$dbLink = dbConnect();
	
	$addressId = dbEscapeString($dbLink, trim($_GET["AddressId"]));
	
	$query = "SELECT * FROM vendor_address WHERE Id = {$addressId} ";
	
	$result = dbRunQuery($dbLink,$query);
	$data = dbGetResult($result);

	$output = array();
	$output['Id'] = intval($data['Id']);
	$output['VendorId'] = intval($data['VendorId']);
	$output['CountryId'] = intval($data['CountryId']);
	$output['Street'] = $data['Street'];
	$output['PostalCode'] = $data['PostalCode'];
	$output['City'] = $data['City'];
	$output['VatTaxNumber'] = $data['VatTaxNumber'];
	$output['CustomsAccountNumber'] = $data['CustomsAccountNumber'];
	
	dbClose($dbLink);	
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	if(!isset($data["VendorId"]))sendResponse(null, "VendorId not specified");
	
	$dbLink = dbConnect();

	$insertData = array();
	$insertData['VendorId']= intval($data['VendorId']);
	$insertData['CountryId'] = intval($data['CountryId']);
	$insertData['Street'] = $data['Street'];
	$insertData['PostalCode'] = $data['PostalCode'];
	$insertData['City'] = $data['City'];
	$insertData['VatTaxNumber'] = $data['VatTaxNumber'];
	$insertData['CustomsAccountNumber'] = $data['CustomsAccountNumber'];
	
	$query = dbBuildInsertQuery($dbLink, "vendor_address", $insertData);
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
	if(!isset($data["AddressId"]))sendResponse(null, "AddressId not specified");
	
	$dbLink = dbConnect();
	
	$addressId = intval($data["AddressId"]);
	
	$insertData = array();
	$insertData['CountryId'] = intval($data['CountryId']);
	$insertData['Street'] = $data['Street'];
	$insertData['PostalCode'] = $data['PostalCode'];
	$insertData['City'] = $data['City'];
	$insertData['VatTaxNumber'] = $data['VatTaxNumber'];
	$insertData['CustomsAccountNumber'] = $data['CustomsAccountNumber'];
	
	$query = dbBuildUpdateQuery($dbLink, "vendor_address", $insertData, "Id = {$addressId}");
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