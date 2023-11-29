<?php
//*************************************************************************************************
// FileName : inventory.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/util/getChildren.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";
require_once __DIR__ . "/util/_barcodeParser.php";

require_once __DIR__ . "/location/_location.php";

if($api->isGet("inventory.view"))
{
    $parameter = $api->getGetData();

    $categoryId = null;
	if(isset($parameter->CategoryId)) $categoryId = intval($parameter->CategoryId);
	if($categoryId !== null) $categories = getChildren("inventory_category", $categoryId);

    $locationIds = null;
    if(isset($parameter->LocationNumber)){
        $location = new Location();
        $locationIds = $location->idsWithChildren($parameter->LocationNumber);
    }

    $baseQuery = <<<STR
        SELECT 
            PicturePath, 
            InvNo, 
            Title, 
            Manufacturer, 
            Type, 
            SerialNumber, 
            Status,
            vendor_displayName(vendor.Id) AS SupplierName 
        FROM `inventory`
        LEFT JOIN `vendor` On vendor.Id = inventory.VendorId
        LEFT JOIN `inventory_category` ON inventory_category.Id = inventory.InventoryCategoryId
    STR;

	$queryParam = array();
	
	if(isset($parameter->InventoryNumber))
	{
        $temp = barcodeParser_InventoryNumber($parameter->InventoryNumber);
		$queryParam[] = "InvNo = {$temp}";
	}
	
	if($locationIds !== null)
	{
		$queryParam[] = "LocationId IN (" . implode(",",$locationIds) . ")";
	}
	
	if(isset($categories))
	{
		$queryParam[] = "InventoryCategoryId IN ({$categories})";
	}

    $result = $database->query($baseQuery, $queryParam);

	global $dataRootPath;
	global $picturePath;
	$pictureRootPath = $dataRootPath.$picturePath."/";
	
	$output = array();
    foreach ($result as $r)
	{
		$item = array();
		$item['PicturePath'] = $pictureRootPath.$r->PicturePath;
		$item['InventoryNumber'] = $r->InvNo;
		$item['InventoryBarcode'] = barcodeFormatter_InventoryNumber($r->InvNo);
		$item['Title'] = $r->Title;
		$item['ManufacturerName'] = $r->Manufacturer;
		$item['Type'] = $r->Type;
		$item['SerialNumber'] = $r->SerialNumber;
		$item['Status'] = $r->Status;
			
		$output[] = $item;
	}

	$api->returnData($output);
}
