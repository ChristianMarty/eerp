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
require_once __DIR__ . "/location/_location.php";

if($api->isGet(Permission::Inventory_List))
{
    $parameter = $api->getGetData();

    $baseQuery = <<<STR
        SELECT 
            PicturePath, 
            InventoryNumber, 
            Title, 
            Manufacturer AS ManufacturerName, 
            inventory_category.Name as CategoryName,
            Type, 
            SerialNumber, 
            Status,
            location_getName(LocationId) AS LocationName
        FROM inventory
        LEFT JOIN vendor On vendor.Id = inventory.VendorId
        LEFT JOIN inventory_category ON inventory_category.Id = inventory.InventoryCategoryId
    STR;

	$queryParam = array();
	
	if(isset($parameter->InventoryNumber)) {
        $temp = \Numbering\parser(\Numbering\Category::Inventory, $parameter->InventoryNumber);
        if ($temp === null) $api->returnData(\Error\parameter('InventoryNumber'));
        $queryParam[] = "InventoryNumber = '$temp'";
	}

    $categoryId = null;
    if(isset($parameter->CategoryId)) {
        $categoryId = intval($parameter->CategoryId);
        if ($categoryId === 0) $api->returnData(\Error\parameter('CategoryId'));
        $categories = getChildren("inventory_category", $categoryId);
        $queryParam[] = "InventoryCategoryId IN ($categories)";
    }

    if(isset($parameter->LocationNumber)) {
        $temp = \Numbering\parser(\Numbering\Category::Location, $parameter->LocationNumber);
        if ($temp === null) $api->returnData(\Error\parameter('LocationNumber'));
        $location = new Location();
        $locationIds = $location->idsWithChildren($temp);
        $queryParam[] = "LocationId IN (" . implode(",",$locationIds) . ")";
    }

    $result = $database->query($baseQuery, $queryParam);
    \Error\checkErrorAndExit($result);

	global $dataRootPath;
	global $picturePath;
	$pictureRootPath = $dataRootPath.$picturePath."/";
    foreach($result as &$item)
	{
		$item->PicturePath = $pictureRootPath.$item->PicturePath;
        $item->InventoryNumber = intval($item->InventoryNumber);
        $item->ItemCode = \Numbering\format(\Numbering\Category::Inventory, $item->InventoryNumber);
	}

	$api->returnData($result);
}
