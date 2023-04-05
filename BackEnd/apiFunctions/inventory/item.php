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
require_once __DIR__ . "/../util/_getDocuments.php";
require_once __DIR__ . "/../util/getPurchaseInformation.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET["InventoryNumber"]))
	{
		$InvNo = $_GET["InventoryNumber"];
		$InvNo = strtolower($InvNo);
		$InvNo = str_replace("inv","",$InvNo);
		$InvNo = str_replace("-","",$InvNo);
	}
	elseif(isset($_GET["SerialNumber"]))
	{
		$SerNo = $_GET["SerialNumber"];
	}
	else
	{
		sendResponse(null,"No inventory item specified");
	}
	
	$locations = getLocations();
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	if(isset($InvNo)) $InvNo = dbEscapeString($dbLink, $InvNo );
	if(isset($SerNo)) $SerNo = dbEscapeString($dbLink, $SerNo );

	$baseQuery = <<<STR
		SELECT inventory.Id AS Id, PicturePath, InvNo, Title, Manufacturer, Type, SerialNumber, PurchaseDate, PurchasePrice, Description, Note, DocumentIds, MacAddressWired, MacAddressWireless, Status,  
		vendor.name AS SupplierName, HomeLocationId, location.LocNr, InventoryCategoryId, inventory.LocationId 
		FROM `inventory`
		LEFT JOIN `vendor` On vendor.Id = inventory.VendorId 
		LEFT JOIN `location` On location.Id = inventory.LocationId 
		LEFT JOIN `inventory_categorie` On inventory_categorie.Id = inventory.InventoryCategoryId 
	STR;

	if(isset($InvNo)) $baseQuery .="WHERE `InvNo` = '".$InvNo."'";
	if(isset($SerNo)) $baseQuery .="WHERE `SerialNumber` = '".$SerNo."'";
	
	global $dataRootPath;
	global $picturePath;

	$pictureRootPath = $dataRootPath.$picturePath."/";
	
	$result = dbRunQuery($dbLink,$baseQuery);
	$r = mysqli_fetch_assoc($result);
	$id = $r['Id'];
	
	$output = array();
	$output['PicturePath'] = $pictureRootPath.$r['PicturePath'];
	$output['InventoryNumber'] = $r['InvNo'];
	$output['InventoryBarcode'] = "Inv-".$r['InvNo'];
	$output['Title'] = $r['Title'];
	$output['ManufacturerName'] = $r['Manufacturer'];
	$output['Type'] = $r['Type'];
	$output['SerialNumber'] = $r['SerialNumber'];
	$output['Description'] = $r['Description'];
	$output['Note'] = $r['Note'];
	$output['MacAddressWired'] = $r['MacAddressWired'];
	$output['MacAddressWireless'] = $r['MacAddressWireless'];
	$output['Status'] = $r['Status'];
	$output['LocationNumber'] = $r['LocNr'];
	$output['CategoryId'] = $r['InventoryCategoryId'];
	$output['LocationName'] = buildLocation($locations, $r['LocationId']);
	$output['LocationPath'] = buildLocationPath($locations, $r['LocationId'], 100);
	$output['HomeLocationName'] = buildLocation($locations, $r['HomeLocationId']);
	$output['HomeLocationPath'] = buildLocationPath($locations, $r['HomeLocationId'], 100);

	// Get Purchase Information
	$query = <<<STR
		SELECT  PoNo, purchasOrder_itemOrder.LineNo AS LineNumber , purchasOrder_itemOrder.Description, purchasOrder_itemOrder.Discount, vendor.Name AS SupplierName, purchasOrder.VendorId AS SupplierId, Price, PurchaseDate, inventory_purchasOrderReference.Quantity,  finance_currency.CurrencyCode AS Currency, ExchangeRate, purchasOrder_itemOrder.Sku AS SupplierPartNumber, inventory_purchasOrderReference.Type AS CostType
		FROM inventory_purchasOrderReference
		LEFT JOIN purchasOrder_itemReceive ON inventory_purchasOrderReference.ReceivalId = purchasOrder_itemReceive.Id
		LEFT JOIN purchasOrder_itemOrder ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id
		LEFT JOIN purchasOrder ON purchasOrder_itemOrder.PurchasOrderId = purchasOrder.Id
		LEFT JOIN vendor ON purchasOrder.VendorId = vendor.Id
		LEFT JOIN finance_currency ON purchasOrder.CurrencyId = finance_currency.Id
		WHERE inventory_purchasOrderReference.InventoryId = {$id}
		ORDER BY PurchaseDate
	STR;
	
	$purchase = array();
	$result = dbRunQuery($dbLink,$query);
	$totalPurchase = 0;
	$totalMaintenance = 0;
	while($por = mysqli_fetch_assoc($result))
	{
		$por["PurchaseOrderNumber"] = $por['PoNo'];
		$por["PurchaseOrderBarcode"] = "PO-".$por['PoNo']."#".$por['LineNumber'];
		$por['PoNo'] ="PO-".$por['PoNo']; 

		$price = ($por["Price"]*$por["ExchangeRate"])*$por['Quantity']*((100 - intval($por['Quantity']))/100);
		if($por['CostType'] == 'Purchase')  $totalPurchase += $price;
		else $totalMaintenance += $price;

		$purchase[] = $por;
	}
	
	if(count($purchase) == 0) // Fallback to legacy data
	{
		$row = array();
		$row["PoNo"] = null;
		$row["LineNumber"] = null;
		$row["Price"] = $r["PurchasePrice"];
		$row["Currency"] = "CHF"; // TODO: Fix this
		$row["SupplierPartNumber"] = null;
		$row["SupplierName"] = $r["SupplierName"];
		$row["SupplierId"] = NULL;
		$row["PurchaseDate"] = $r["PurchaseDate"];
		$row["OrderReference"] = null;
		$row["VendorId"] = 0;
		$row["Quantity"] = 1;
		$row["CostType"] = 'Legacy Purchase';
		$row["Description"] = "";
		
		$totalPrice = $row["Price"];
		
		$purchase[] = $row;
	}
	
	$output["PurchaseInformation"] = $purchase;

	$output["TotalPurchaseCost"] =  round($totalPurchase, 2);
	$output["TotalMaintenanceCost"] =  round($totalMaintenance, 2);
	$output["TotalCostOfOwnership"] =  round($totalPurchase+$totalMaintenance, 2);
	$output["TotalCurrency"] = "CHF"; //TODO: Fix  $purchase[0]["Currency"];
	
	// Get Accessory
	
	$query  = "SELECT AccessoryNumber, Description, Note, Labeled FROM inventory_accessory ";
	$query .= "WHERE InventoryId = ".$id;
	$query .= " ORDER BY AccessoryNumber ASC";
	
	$accessory = array();
	$result = dbRunQuery($dbLink,$query);
	while($acs = mysqli_fetch_assoc($result))
	{
		$acs["AccessoryBarcode"] = $output['InventoryBarcode']."-".$acs["AccessoryNumber"];
		if($acs["Labeled"] == "0") $acs["Labeled"] = false;
		else $acs["Labeled"] = true;
		$accessory[] = $acs;
	}
	
	$output["Accessory"] = $accessory;
	
	// Get Documents
	if(isset($r['DocumentIds'])) $DocIds = $r['DocumentIds'];
	else $DocIds = null;
	unset($r['DocumentIds']);
	$output["Documents"] = getDocuments($DocIds);
	
	// Get History
	$History = array();
	global $documentRootPath;

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
				$Documents[] = $j;
			}
		}
		$r['Documents'] = $Documents;
		
		unset($r['DocumentIds']);
		unset($r['Id']);
		unset($r['InventoryId']);
		
		$History[] = $r;
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

	$sqlData = array();
	$sqlData['InvNo']['raw'] = "(SELECT generateItemNumber())";
	$sqlData['Title'] = $data['Title'];
	$sqlData['Manufacturer'] = $data['ManufacturerName'];
	$sqlData['Type'] = $data['Type'];
	$sqlData['SerialNumber'] = $data['SerialNumber'];
	$sqlData['LocationId']['raw'] = "(SELECT Id FROM location WHERE LocNr = ".dbEscapeString($dbLink,$data['LocationNumber']).")";
	$sqlData['InventoryCategoryId'] = intval($data['CategoryId']);

	$query = dbBuildInsertQuery($dbLink,"inventory", $sqlData);

	$query .= " SELECT `InvNo` FROM `inventory` WHERE `Id` = LAST_INSERT_ID();";
	
	$error = null;
	$output = null;
	
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output = $row[0];
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
