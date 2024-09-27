<?php
//*************************************************************************************************
// FileName : search.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/location/_location.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet())
{
	$parameter = $api->getGetData();
	if(!isset($parameter->search)) $api->returnParameterMissingError("search");

	$search = trim(strtolower($parameter->search));
	$parts = explode('-',$search);
	
	$data = array();

	$found = false;

	if(count($parts) >= 2)  // Search for barcodes
	{
		$category = "";	
		$prefix = "";

		$query = "SELECT * FROM numbering ";

		$result = $database->query($query);
		foreach($result as $item)
		{
			if(strtolower($item->Prefix) == $parts[0])
			{
				$category = $item->Category;
				$prefix = $item->Prefix;
				$found = true;
				break;
			}
		}

		if($found)
		{
			$data["Category"] = $category;
			$data["Item"] = $prefix . "-" . $parts[1];
			$data["RedirectCode"] = $prefix . "-" . $parts[1];

			$api->returnData([$data]);
		}
	}

	// Search everywhere else
	$output = array_merge(
		search_manufacturerPartNumber($search),
		search_assemblySerialNumber($search),
		search_manufacturerPartItem($search),
		manufacturerPartSeries($search),
		search_vendor($search),
        search_supplierPartNumber($search)
	);

	$api->returnData($output);
}


function manufacturerPartSeries(string $input): array
{
	global $database;

	$input = $database->escape($input);

	$query = <<<STR
		SELECT
			manufacturerPart_series.Id, 
			Title, 
			Description, 
			vendor_displayName(vendor.Id) AS VendorName
		FROM manufacturerPart_series 
		LEFT JOIN vendor on manufacturerPart_series.VendorId = vendor.Id
		WHERE Title LIKE $input OR  Description LIKE $input
	STR;
	$result = $database->query($query);

	$output = array();
	foreach($result as $item)
	{
		$temp = array();
		$temp["Category"] = 'ManufacturerPartItem';
		$temp["Item"] = $item->VendorName." - ".$item->Title;
		$temp["RedirectCode"] = $item->Id;
		$temp["Description"] = $item->Description;
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}

function search_vendor(string $input): array
{
	global $database;
	$input = $database->escape($input);

	$query = <<<STR
		SELECT 
			Id, 
			FullName, 
			ShortName,
			AbbreviatedName,
			vendor_displayName(vendor.Id) AS VendorName
		FROM vendor 
		WHERE FullName LIKE $input OR ShortName LIKE $input OR AbbreviatedName LIKE $input
	STR;
	$result = $database->query($query);

	$output = array();
	foreach($result as $item)
	{
		$temp = array();
		$temp["Category"] = 'Vendor';
		$temp["Item"] = $item->VendorName;
		$temp["RedirectCode"] = $item->Id;
		$temp["Description"] = '';
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}

function search_manufacturerPartItem(string $input): array
{
	global $database;
	$input = $database->escape($input);

	$query = <<<STR
		SELECT manufacturerPart_item.Id, Number, vendor_displayName(vendor.Id) AS VendorName
		FROM manufacturerPart_item 
		LEFT JOIN vendor on manufacturerPart_item.VendorId = vendor.Id
		WHERE Number LIKE $input
	STR;
	$result = $database->query($query);

	$output = array();
	foreach($result as $item)
	{
		$temp = array();
		$temp["Category"] = 'ManufacturerPartItem';
		$temp["Item"] = $item->VendorName." - ".$item->Number;
		$temp["RedirectCode"] = $item->Id;
		$temp["Description"] = '';
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}

function search_manufacturerPartNumber(string $input): array
{
	global $database;
	$input = $database->escape($input);

	$query = "SELECT Id, Number FROM manufacturerPart_partNumber WHERE Number LIKE $input";

	$result = $database->query($query);

	$output = array();
	foreach($result as $item)
	{
		$temp = array();
		$temp["Category"] = 'ManufacturerPartNumber';
		$temp["Item"] = $item->Number;
		$temp["RedirectCode"] = $item->Id;
		$temp["Description"] = '';
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}

function search_assemblySerialNumber(string $input): array
{
	global $database;
	$input = $database->escape($input);

	$query = "SELECT Id, AssemblyUnitNumber, SerialNumber FROM assembly_unit WHERE SerialNumber LIKE $input";

	$result = $database->query($query);

	$output = array();
	foreach($result as $item)
	{
		$temp = array();
		$temp["Category"] = 'AssemblyUnit';
		$temp["Item"] = $item->SerialNumber;
		$temp["RedirectCode"] = barcodeFormatter_AssemblyUnitNumber($item->AssemblyUnitNumber);
		$temp["Description"] = '';
		$temp["LocationPath"] = '';

		$output[] = $temp;
	}
	return $output;
}

function search_supplierPartNumber(string $input): array
{
    global $database;
    $input = $database->escape($input);

    $query = <<< QUERY
        SELECT 
            manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
            SupplierPartNumber 
        FROM supplierPart
        LEFT JOIN manufacturerPart_partNumber ON supplierPart.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
        WHERE SupplierPartNumber LIKE $input
    QUERY;


    $result = $database->query($query);

    $output = array();
    foreach($result as $item)
    {
        $temp = array();
        $temp["Category"] = 'SupplierPartNumber';
        $temp["Item"] = $item->SupplierPartNumber;
        $temp["RedirectCode"] = $item->ManufacturerPartNumberId;
        $temp["Description"] = '';
        $temp["LocationPath"] = '';

        $output[] = $temp;
    }
    return $output;
}
