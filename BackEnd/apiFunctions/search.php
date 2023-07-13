<?php
//*************************************************************************************************
// FileName : search.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/util/location.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["search"])) sendResponse(null,"Search term not specified");
	
	$search = trim(strtolower($_GET["search"]));
	$parts = explode('-',$search);
	
	$data = array();

	$found = false;

	if(count($parts) >= 2)  // Search for barcodes
	{
		$category = "";	
		$prefix = "";
		
		$dbLink = dbConnect();
		
		$query = "SELECT * FROM numbering ";
		$result = dbRunQuery($dbLink,$query);
		
		while($r = mysqli_fetch_assoc($result)) 
		{
			if(strtolower($r['Prefix']) == $parts[0])
			{
				$category = $r['Category'];
				$prefix = $r['Prefix'];
				$found = true;
				break;
			}
		}
		dbClose($dbLink);

		if($found)
		{
			$data["Category"] = $category;
			$data["Item"] = $prefix . "-" . $parts[1];
			$data["RedirectCode"] = $prefix . "-" . $parts[1];

			$output = array();
			$output[] = $data;
			sendResponse($output);
		}
	}

	$dbLink = dbConnect();
	// Search everywhere else
	$output = array_merge(
		search_manufacturerPartNumber($dbLink, $search),
		search_assemblySerialNumber($dbLink, $search),
		search_manufacturerPartItem($dbLink, $search),
		manufacturerPartSeries($dbLink, $search),
		search_vendor($dbLink, $search)
	);
	dbClose($dbLink);
	sendResponse($output);
}


function manufacturerPartSeries($dbLink, $input): array
{
	$input = dbEscapeString($dbLink,$input);

	$query = <<<STR
		SELECT manufacturerPart_series.Id, Title, Description, vendor_displayName(vendor.Id) AS VendorName
		FROM manufacturerPart_series 
		LEFT JOIN vendor on manufacturerPart_series.VendorId = vendor.Id
		WHERE Title LIKE '$input' OR  Description LIKE '$input'
	STR;
	$result = dbRunQuery($dbLink,$query);

	$output = array();
	while($r = mysqli_fetch_assoc($result))
	{
		$temp = array();
		$temp["Category"] = 'ManufacturerPartItem';
		$temp["Item"] = $r['VendorName']." - ".$r['Title'];
		$temp["RedirectCode"] = $r['Id'];
		$temp["Description"] = $r['Description'];
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}

function search_vendor($dbLink, $input): array
{

	$input = dbEscapeString($dbLink,$input);

	$query = <<<STR
		SELECT Id, Name, vendor_displayName(vendor.Id) AS VendorName
		FROM vendor 
		WHERE Name LIKE '$input' OR ShortName LIKE '$input'
	STR;
	$result = dbRunQuery($dbLink,$query);

	$output = array();

	while($r = mysqli_fetch_assoc($result))
	{
		$temp = array();
		$temp["Category"] = 'Vendor';
		$temp["Item"] = $r['VendorName'];
		$temp["RedirectCode"] = $r['Id'];
		$temp["Description"] = '';
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}

function search_manufacturerPartItem($dbLink, $input): array
{
	$input = dbEscapeString($dbLink,$input);

	$query = <<<STR
		SELECT manufacturerPart_item.Id, Number, vendor_displayName(vendor.Id) AS VendorName
		FROM manufacturerPart_item 
		LEFT JOIN vendor on manufacturerPart_item.VendorId = vendor.Id
		WHERE Number LIKE '$input'
	STR;
	$result = dbRunQuery($dbLink,$query);

	$output = array();

	while($r = mysqli_fetch_assoc($result))
	{
		$temp = array();
		$temp["Category"] = 'ManufacturerPartItem';
		$temp["Item"] = $r['VendorName']." - ".$r['Number'];
		$temp["RedirectCode"] = $r['Id'];
		$temp["Description"] = '';
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}

function search_manufacturerPartNumber($dbLink, $input): array
{
	$input = dbEscapeString($dbLink,$input);

	$query = "SELECT Id, Number FROM manufacturerPart_partNumber WHERE Number LIKE '$input'";
	$result = dbRunQuery($dbLink,$query);

	$output = array();

	while($r = mysqli_fetch_assoc($result))
	{
		$temp = array();
		$temp["Category"] = 'ManufacturerPartNumber';
		$temp["Item"] = $r['Number'];
		$temp["RedirectCode"] = $r['Id'];
		$temp["Description"] = '';
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}


function search_assemblySerialNumber($dbLink, $input): array
{
	$input = dbEscapeString($dbLink,$input);

	$query = "SELECT Id, AssemblyUnitNumber, SerialNumber FROM assembly_unit WHERE SerialNumber LIKE '$input'";
	$result = dbRunQuery($dbLink,$query);

	$output = array();

	while($r = mysqli_fetch_assoc($result))
	{
		$temp = array();
		$temp["Category"] = 'AssemblyUnit';
		$temp["Item"] = $r['SerialNumber'];
		$temp["RedirectCode"] = barcodeFormatter_AssemblyUnitNumber($r['AssemblyUnitNumber']);
		$temp["Description"] = '';
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}
?>
