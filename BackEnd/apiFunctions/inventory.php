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
	if(isset($_GET["Category"]))
	{	
		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$categoryId = 0;
		$category = dbEscapeString($dbLink,$_GET["Category"] );
		$query = "SELECT `Id` FROM `inventory_categorie` WHERE `Name`= '".$category."'";
		$result = dbRunQuery($dbLink,$query);
		while($r = mysqli_fetch_assoc($result))
		{
			$categoryId = $r['Id'];
		}			
		
		dbClose($dbLink);
		
		$categories =  getChildren("inventory_categorie", $categoryId);
	}
	
	if(isset($_GET["LocNr"]))
	{	
		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$locNr = $_GET["LocNr"];
		$locNr = str_replace("Loc","",$locNr);
		$locNr = str_replace("-","",$locNr);
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
	$baseQuery .="PicturePath, InvNo, Title, Manufacturer, Type, SerialNumber, PurchaseDate, PurchasePrice, Status,";
	$baseQuery .="supplier.name AS SupplierName, ";
	$baseQuery .="location_getName(LocationId) AS LocationName ";
	$baseQuery .="FROM `inventory` ";
	$baseQuery .="LEFT JOIN `supplier` On supplier.Id = inventory.SupplierId ";
	$baseQuery .="LEFT JOIN `inventory_categorie` On inventory_categorie.Id = inventory.InventoryCategoryId ";
	
	$queryParam = array();
	
	if(isset($_GET["InvNo"]))
	{
		$code = $_GET["InvNo"];
		$code = str_replace("Inv","",$code);
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

	$output = array();
	$PictureRootPath = $dataRootPath."/data/pictures/";

	$query = dbBuildQuery($dbLink,$baseQuery,$queryParam);


	$result = dbRunQuery($dbLink,$query);
	while($r = dbGetResult($result)) 
	{
		$r['InvNo'] = "Inv-".$r['InvNo'];
		$r['PicturePath'] = $PictureRootPath.$r['PicturePath'];
		$output[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($output);
}


?>