<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $database;
global $api;

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

require_once __DIR__. "/alias/_alias.php";
require_once __DIR__. "/contact/_contact.php";

if($api->isGet("vendor.view"))
{
	if(!isset($_GET["VendorId"]))sendResponse(null, "VendorId not specified");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$vendorId = dbEscapeString($dbLink, trim($_GET["VendorId"]));

    $query = <<< STR
        SELECT
            vendor.Id as VendorId,
            vendor.FullName as FullName,
            vendor.ParentId as VendorParentId,
            vendor.CustomerNumber as VendorCustomerNumber,
            vendor_displayName(vendor.Id) as VendorDisplayName,
            vendor.ShortName as VendorShortName,
            vendor.AbbreviatedName as AbbreviatedName,
            vendor.IsSupplier as VendorIsSupplier,
            vendor.IsManufacturer as VendorIsManufacturer,
            vendor.IsContractor as VendorIsContractor,
            vendor.ParentId as VendorParentId,
            vendor_displayName(parent.Id) AS ParentName
        FROM vendor 
        LEFT JOIN vendor parent on parent.Id = vendor.ParentId
        WHERE vendor.Id = {$vendorId}
    STR;

    $result = dbRunQuery($dbLink,$query);
	
	$data = dbGetResult($result);
	
	$output = array();
	
	$output['Id'] = intval($data['VendorId']);
	$output['ParentId'] = intval($data['VendorParentId']);
	if($output['ParentId'] == 0){
        $output['ParentId'] = null;
        $output['ParentName'] = null;
    }else{
        $output['ParentName'] = $data['ParentName'];
    }
	$output['FullName'] = $data['FullName'];
	$output['CustomerNumber'] = $data['VendorCustomerNumber'];
	$output['DisplayName'] = $data['VendorDisplayName'];
    $output['AbbreviatedName'] = $data['AbbreviatedName'];
	if($data['VendorIsSupplier'] != 0) $output['IsSupplier'] = true;
	else $output['IsSupplier'] = false;
	if($data['VendorIsManufacturer'] != 0) $output['IsManufacturer'] = true;
	else $output['IsManufacturer'] = false;
    if($data['VendorIsContractor'] != 0) $output['IsContractor'] = true;
    else $output['IsContractor'] = false;


	$output['Alias'] = \vendor\alias::aliasesForVendor($vendorId);
	
	// Get Children
	$query = "SELECT * FROM vendor WHERE ParentId = {$vendorId} ";
	
	$result = dbRunQuery($dbLink,$query);
	$children = array();
	while($r = dbGetResult($result))
	{
		$temp = array();
		$temp['Id'] = intval($r['Id']);
		$temp['Name'] = $r['Name'];
		$temp['CustomerNumber'] = $r['CustomerNumber'];
		$children[] = $temp;
	}
	
	$output['Children'] = $children;

	// Get Addresses
    $query =  <<<STR
        SELECT 
            vendor_address.Id, 
            PhonePrefix, 
            CountryCode, 
            Name AS CountryName, 
            CountryId, 
            PostalCode, 
            City, 
            Street, 
            VatTaxNumber, 
            CustomsAccountNumber 
        FROM vendor_address
        LEFT JOIN country ON country.Id = CountryId
        WHERE VendorId = $vendorId
    STR;

	$result = dbRunQuery($dbLink,$query);
	$address = array();
	while($r = dbGetResult($result))
	{
		$r['Id'] = intval($r['Id']);
		$r['CountryId'] = intval($r['CountryId']);
		$address[] = $r;
	}
	
	$output['Address'] = $address;
	
	// Get Contacts
	$query  = "SELECT Id, Gender, FirstName, LastName, JobTitle, Language, Phone, `E-Mail` AS EMail FROM vendor_contact ";
	$query .= "WHERE VendorId = {$vendorId} ";
	
	$result = dbRunQuery($dbLink,$query);
	$contact = array();
	while($r = dbGetResult($result))
	{
		$contact[] = $r;
	}
	
	$output['Contact'] = $contact;
	
	dbClose($dbLink);	
	sendResponse($output);
}
else if($api->isPatch())
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	
	$vendorId = dbEscapeString($dbLink,$data['VendorId']);
	
	$insertData = array();
    $insertData['FullName']  = dbEscapeString($dbLink,trim($data['FullName']));
    $insertData['DisplayName']  = dbEscapeString($dbLink,trim($data['DisplayName']));
    $insertData['AbbreviatedName']  = dbEscapeString($dbLink,trim($data['AbbreviatedName']));
    $insertData['CustomerNumber']  = dbEscapeString($dbLink,trim($data['CustomerNumber']));
	
	if($data['IsSupplier']) $insertData['IsSupplier']['raw']  = "b'1'";
	else $insertData['IsSupplier']['raw']  = "b'0'";
	if($data['IsManufacturer']) $insertData['IsManufacturer']['raw']  = "b'1'";
	else $insertData['IsManufacturer']['raw']  = "b'0'";
    if($data['IsContractor']) $insertData['IsContractor']['raw']  = "b'1'";
    else $insertData['IsContractor']['raw']  = "b'0'";

    $insertData['ParentId']['raw'] = dbIntegerNull($data['ParentId']);
	
	
	$query = dbBuildUpdateQuery($dbLink, "vendor", $insertData, "Id = {$vendorId}");
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if(!$result)
	{
		$error = "Error description: " . dbGetErrorString($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse(null, $error);
}
else if($api->isPost())
{
    $data = $api->getPostData();

    $dbLink = dbConnect();

    $vendorName = dbEscapeString($dbLink,$data['FullName']);
    $insertData['FullName']  = trim($vendorName);

    if($data['IsSupplier']) $insertData['IsSupplier']['raw']  = "b'1'";
    else $insertData['IsSupplier']['raw']  = "b'0'";
    if($data['IsManufacturer']) $insertData['IsManufacturer']['raw']  = "b'1'";
    else $insertData['IsManufacturer']['raw']  = "b'0'";
    if($data['IsContractor']) $insertData['IsContractor']['raw']  = "b'1'";
    else $insertData['IsContractor']['raw']  = "b'0'";

    $query = dbBuildInsertQuery($dbLink, "vendor", $insertData);

    $result = dbRunQuery($dbLink,$query);

    $error = null;
    $data = array();
    if(!$result)
    {
        $error = "Error description: " . dbGetErrorString($dbLink);
    }

    $query = "SELECT Id FROM vendor WHERE Id = LAST_INSERT_ID();";
    $result = dbRunQuery($dbLink,$query);

    $result = dbGetResult($result);

    if(isset($result['Id']))
    {
        $data['VendorId'] = $result['Id'];
    }
    else
    {
        $error = "Vendor creation failed! Maybe it already exists? ";
    }

    dbClose($dbLink);
    sendResponse($data, $error);
}
	
?>