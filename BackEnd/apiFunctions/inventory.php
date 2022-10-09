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
		if($dbLink == null) return null;
		
		$locNr = $_GET["LocationNumber"];
		$locNr = strtolower($locNr);
		$locNr = str_replace("loc-","",$locNr);
		$locNr = dbEscapeString($dbLink, $locNr );
		
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
	if($dbLink == null) return null;
	
	$baseQuery = "SELECT ";
	$baseQuery .="PicturePath, InvNo, Title, Manufacturer, Type, SerialNumber, Status,";
	$baseQuery .="vendor.name AS SupplierName ";
	$baseQuery .="FROM `inventory` ";
	$baseQuery .="LEFT JOIN `vendor` On vendor.Id = inventory.VendorId ";
	$baseQuery .="LEFT JOIN `inventory_categorie` On inventory_categorie.Id = inventory.InventoryCategoryId ";
	
	$queryParam = array();
	
	if(isset($_GET["InventoryNumber"]))
	{
		$code = $_GET["InventoryNumber"];
		$code = strtolower($code);
		$code = str_replace("inv","",$code);
		$code = str_replace("-","",$code);
		$temp = dbEscapeString($dbLink, $code );
		array_push($queryParam, "InvNo LIKE '".$temp."'");
	}
	
	if(isset( $locationIds))
	{
		array_push($queryParam, "LocationId IN (".$locationIds.")");
	}
	
	if(isset($categories))
	{
		array_push($queryParam, "InventoryCategoryId IN (".$categories.")");
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
		$item['InventoryBarcode'] = "Inv-".$r['InvNo'];
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