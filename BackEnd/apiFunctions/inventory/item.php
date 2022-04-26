<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/inventory/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/location.php";
require_once __DIR__ . "/../util/getDocuments.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET["InvNo"]))
	{
		$InvNo = $_GET["InvNo"];
		$InvNo = strtolower($InvNo);
		$InvNo = str_replace("inv","",$InvNo);
		$InvNo = str_replace("-","",$InvNo);
	}
	elseif(isset($_GET["SerNo"]))
	{
		$SerNo = $_GET["SerNo"];
	}
	else
	{
		sendResponse($output,"No inventory item specified");
	}
	
	$locations = getLocations();
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	if(isset($InvNo)) $InvNo = dbEscapeString($dbLink, $InvNo );
	if(isset($SerNo)) $SerNo = dbEscapeString($dbLink, $SerNo );
	
	$baseQuery = "SELECT ";
	$baseQuery .="inventory.Id AS Id, ReceivalId, PicturePath, InvNo, Title, Manufacturer, Type, SerialNumber, PurchaseDate, PurchasePrice, Description, Note, DocumentIds, MacAddressWired, MacAddressWireless, Status,  ";
	$baseQuery .="vendor.name AS SupplierName, LocationId, HomeLocationId ";
	$baseQuery .="FROM `inventory` ";
	$baseQuery .="LEFT JOIN `vendor` On vendor.Id = inventory.VendorId ";
	$baseQuery .="LEFT JOIN `inventory_categorie` On inventory_categorie.Id = inventory.InventoryCategoryId ";
	
	if(isset($InvNo)) $baseQuery .="WHERE `InvNo` = '".$InvNo."'";
	if(isset($SerNo)) $baseQuery .="WHERE `SerialNumber` = '".$SerNo."'";
		
	$output = array();
	$PictureRootPath = $dataRootPath."/data/pictures/";
	
	$result = dbRunQuery($dbLink,$baseQuery);
	$r = mysqli_fetch_assoc($result);
	
	$r['InvNo'] = "Inv-".$r['InvNo'];
	$r['PicturePath'] = $PictureRootPath.$r['PicturePath'];
	
	$r['Location'] =  buildLocation($locations, $r['LocationId']);
	$r['LocationPath'] = buildLocationPath($locations, $r['LocationId'], 100);
	$r['HomeLocation'] = buildLocation($locations, $r['HomeLocationId']);
	$r['HomeLocationPath'] = buildLocationPath($locations, $r['HomeLocationId'], 100);
	
	$id = $r['Id'];
	unset($r['Id']);
	
	$output = $r;
	
	// Get Documents
	if(isset($r['DocumentIds'])) $DocIds = $r['DocumentIds'];
	else $DocIds = null;
	unset($r['DocumentIds']);
	$output["Documents"] = getDocuments($DocIds);
	
	// Get History
	
	$History = array();
	
	$baseQuery = "SELECT * FROM `inventory_history` WHERE InventoryId = ".$id." ORDER BY `Date` ASC";
		
	$result = dbRunQuery($dbLink,$baseQuery);
	while($r = mysqli_fetch_assoc($result))
	{
		$Documents = array();

		if(isset($r['DocumentIds'])) $DocIds = explode(",",$r['DocumentIds']);
		else $DocIds = null;
		
		if(!empty($DocIds))
		{
			$baseQuery = "SELECT * FROM `document` WHERE Id IN(".implode(", ",$DocIds).")";
	
			$result2 = dbRunQuery($dbLink,$baseQuery);
			while($j = mysqli_fetch_assoc($result2))
			{
				$j['Path'] = $documentRootPath."/".$j['Type']."/".$j['Path'];
				array_push($Documents, $j);
			}
		}
		$r['Documents'] = $Documents;
		
		unset($r['DocumentIds']);
		unset($r['Id']);
		unset($r['InventoryId']);
		
		array_push($History, $r);
	}
	
	$output["History"] = $History;
	
	dbClose($dbLink);	
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$baseQuery = "INSERT INTO `inventory`";
	
	$locationId = dbEscapeString($dbLink,$data['data']['LocationId']);
	unset($data['data']['LocationId']);

	$inventoryCategoryId = dbEscapeString($dbLink,$data['data']['InventoryCategoryId']);
	unset($data['data']['InventoryCategoryId']);
	
	
	$supplierId = dbEscapeString($dbLink,$data['data']['SupplierId']);
	unset($data['data']['SupplierId']);
	
	$date = date_create($data['data']['PurchaseDate']);
	$data['data']['PurchaseDate'] = date_format($date, 'Y-m-d');
	
	$columns = "";
	$values = "";

	foreach ($data['data'] as $key => $value) 
	{
		$columns .= "`".dbEscapeString($dbLink, $key )."`,";
		$values  .= "'".dbEscapeString($dbLink, $value )."',";
	}
	

	$columns .= "`LocationId`,";
	$values .= "'".$locationId."',";
	
	$columns .= "`InventoryCategoryId`,";
	$values .= "'".$inventoryCategoryId."',";
	
	$columns .= "`VendorId`";
	$values .= "'".$supplierId."'";
	
	$query =  $baseQuery." (".$columns.") VALUES (".$values.");"; 

	$query .= " SELECT `InvNo` FROM `inventory` WHERE `Id` = LAST_INSERT_ID();";
	
	$error = null;
	
	
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output["InvNo"] = $row[0];
				}
				mysqli_free_result($result);
			}
			if(!mysqli_more_results($dbLink)) break;
		} while (mysqli_next_result($dbLink));
	}
	else
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse($output,$error);
}

?>
