<?php
//*************************************************************************************************
// FileName : inventory.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2021
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/util/getChildren.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";
require_once __DIR__ . "/util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET["CategoryId"]))
	{
		$categoryId = intval($_GET["CategoryId"]);
	}
	
	if(isset($categoryId))$categories =  getChildren("inventory_categorie", $categoryId);
	
	if(isset($_GET["LocationNumber"]))
	{	
		$dbLink = dbConnect();

        $locNr = barcodeFormatter_LocationNumber($_GET["LocationNumber"]);
		
		$locationIds = 0;
		$query = "SELECT `Id` FROM `location` WHERE `LocNr`= '".$locNr."'";
		$result = dbRunQuery($dbLink,$query);
		while($r = dbGetResult($result))
		{
			$locationIds = $r['Id'];
		}			
		
		dbClose($dbLink);
		
		$locationIds =  getChildren("location", $locationIds);
	}
	
	$dbLink = dbConnect();

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
        LEFT JOIN `inventory_categorie` On inventory_categorie.Id = inventory.InventoryCategoryId
    STR;

	$queryParam = array();
	
	if(isset($_GET["InventoryNumber"]))
	{
        $temp = barcodeFormatter_InventoryNumber($_GET["InventoryNumber"]);
		$queryParam[] = "InvNo LIKE '" . $temp . "'";
	}
	
	if(isset( $locationIds))
	{
		$queryParam[] = "LocationId IN (" . $locationIds . ")";
	}
	
	if(isset($categories))
	{
		$queryParam[] = "InventoryCategoryId IN (" . $categories . ")";
	}

	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	$result = dbRunQuery($dbLink,$query);
	
	global $dataRootPath;
	global $picturePath;
	
	$pictureRootPath = $dataRootPath.$picturePath."/";
	
	$output = array();
	
	while($r = dbGetResult($result)) 
	{
		$item = array();
		$item['PicturePath'] = $pictureRootPath.$r['PicturePath'];
		$item['InventoryNumber'] = $r['InvNo'];
		$item['InventoryBarcode'] = barcodeFormatter_InventoryNumber($r['InvNo']);
		$item['Title'] = $r['Title'];
		$item['ManufacturerName'] = $r['Manufacturer'];
		$item['Type'] = $r['Type'];
		$item['SerialNumber'] = $r['SerialNumber'];
		$item['Status'] = $r['Status'];
			
		$output[] = $item;
	}

	dbClose($dbLink);	
	sendResponse($output);
}
?>