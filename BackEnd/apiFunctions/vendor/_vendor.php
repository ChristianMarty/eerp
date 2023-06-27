<?php
//*************************************************************************************************
// FileName : _vendor.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 24.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require __DIR__ . "/../../config.php";

function vendor_getIdFromName($dbLink, $name): int
{
    $name = dbEscapeString($dbLink,$name);
    $query = <<<STR
        CALL `vendor_idFromName`('$name')
    STR;
    $result = dbRunQuery($dbLink,$query);

    return intval(mysqli_fetch_assoc($result)['Id']);
}

function vendor_getContact($vendorContactId): ?array
{
	$dbLink = dbConnect();

    $vendorContactId = intval($vendorContactId);

    $query = <<<STR
        SELECT
            *,
           vendor.Name AS VendorName, 
           country.Name AS CountryName  
        FROM vendor
        LEFT JOIN vendor_address ON vendor.Id = vendor_address.VendorId 
        LEFT JOIN vendor_contact ON vendor.Id = vendor_contact.VendorId
        LEFT JOIN country ON country.Id = vendor_address.CountryId
        WHERE  vendor_contact.Id = $vendorContactId
    STR;

	$result = dbRunQuery($dbLink,$query);
	
	$vendor = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$vendor = $r;
	}

	dbClose($dbLink);	
	
	return $vendor;
}

function vendor_getAddress($vendorAddressId): ?array
{
	$dbLink = dbConnect();
    $vendorAddressId = intval($vendorAddressId);

    $query = <<<STR
        SELECT
            *,
           vendor.Name AS VendorName, 
           country.Name AS CountryName  
        FROM vendor
        LEFT JOIN vendor_address ON  vendor.Id = vendor_address.VendorId
        LEFT JOIN country ON country.Id = vendor_address.CountryId
        WHERE  vendor_address.Id = $vendorAddressId
    STR;
	
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