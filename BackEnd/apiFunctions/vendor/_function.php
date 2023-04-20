<?php
//*************************************************************************************************
// FileName : _function.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require __DIR__ . "/../../config.php";

function getVendorContact($vendorContactId): ?array
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query = "SELECT *, vendor.Name AS VendorName, country.Name AS CountryName  FROM vendor ";
	$query .= "LEFT JOIN vendor_address ON vendor.Id = vendor_address.VendorId ";
	$query .= "LEFT JOIN vendor_contact ON vendor.Id = vendor_contact.VendorId ";
	$query .= "LEFT JOIN country ON country.Id = vendor_address.CountryId ";
	$query .= "WHERE  vendor_contact.Id = ".intval($vendorContactId);

	$result = dbRunQuery($dbLink,$query);
	
	$vendor = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$vendor = $r;
	}

	dbClose($dbLink);	
	
	return $vendor;
}

function getVendorAddress($vendorAddressId): ?array
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT *, vendor.Name AS VendorName, country.Name AS CountryName  FROM vendor ";
	$query .= "LEFT JOIN vendor_address ON  vendor.Id = vendor_address.VendorId ";
	$query .= "LEFT JOIN country ON country.Id = vendor_address.CountryId ";
	$query .= "WHERE  vendor_address.Id = ".intval($vendorAddressId);
	
	$result = dbRunQuery($dbLink,$query);
	
	$vendor = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$vendor = $r;
	}

	dbClose($dbLink);	
	
	return $vendor;
}

?>