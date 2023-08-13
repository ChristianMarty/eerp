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
require_once __DIR__ . "/../location/_location.php";
require_once __DIR__ . "/../util/_getDocuments.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/getPurchaseInformation.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(isset($_GET["InventoryNumber"]))
	{
		$InvNo = barcodeParser_InventoryNumber($_GET["InventoryNumber"]);
	}
	elseif(isset($_GET["SerialNumber"]))
	{
		$SerNo = $_GET["SerialNumber"];
	}
	else
	{
		sendResponse(null,"No inventory item specified");
	}

	$dbLink = dbConnect();
	
	if(isset($InvNo)) $InvNo = dbEscapeString($dbLink, $InvNo );
	if(isset($SerNo)) $SerNo = dbEscapeString($dbLink, $SerNo );

	$baseQuery = <<<STR
		SELECT 
		    inventory.Id AS Id, 
		    PicturePath, 
		    InvNo, 
		    inventory.Title, 
		    inventory.Manufacturer, 
		    inventory.Type, 
		    SerialNumber, 
		    PurchaseDate, 
		    PurchasePrice, 
		    inventory.Description, 
		    inventory.Note, 
		    inventory.DocumentIds, 
		    MacAddressWired, 
		    MacAddressWireless, 
		    Status,  
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
	$output['InventoryBarcode'] = barcodeFormatter_InventoryNumber($r['InvNo']);
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
	$output['LocationName'] = location_getName( $r['LocationId']);
	$output['LocationPath'] = location_getPath( $r['LocationId']);
	$output['HomeLocationName'] = location_getName( $r['HomeLocationId']);
	$output['HomeLocationPath'] = location_getPath( $r['HomeLocationId']);

	// Get Purchase Information
	$query = <<<STR
		SELECT  
		    PoNo, 
		    purchaseOrder_itemOrder.LineNo AS LineNumber , 
		    purchaseOrder_itemOrder.Description, 
		    purchaseOrder_itemOrder.Discount, 
		    vendor.Name AS SupplierName, 
		    purchaseOrder.VendorId AS SupplierId, 
		    Price, 
		    PurchaseDate, 
		    inventory_purchaseOrderReference.Quantity,  
		    finance_currency.CurrencyCode AS Currency, 
		    ExchangeRate, 
		    purchaseOrder_itemOrder.Sku AS SupplierPartNumber, 
		    inventory_purchaseOrderReference.Type AS CostType
		FROM inventory_purchaseOrderReference
		LEFT JOIN purchaseOrder_itemReceive ON inventory_purchaseOrderReference.ReceivalId = purchaseOrder_itemReceive.Id
		LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id
		LEFT JOIN purchaseOrder ON purchaseOrder_itemOrder.PurchaseOrderId = purchaseOrder.Id
		LEFT JOIN vendor ON purchaseOrder.VendorId = vendor.Id
		LEFT JOIN finance_currency ON purchaseOrder.CurrencyId = finance_currency.Id
		WHERE inventory_purchaseOrderReference.InventoryId = {$id}
		ORDER BY PurchaseDate
	STR;
	
	$purchase = array();
	$result = dbRunQuery($dbLink,$query);
	$totalPurchase = 0;
	$totalMaintenance = 0;
	while($por = mysqli_fetch_assoc($result))
	{
		$por["PurchaseOrderNumber"] = $por['PoNo'];
		$por["PurchaseOrderBarcode"] = barcodeFormatter_PurchaseOrderNumber($por['PoNo'], $por['LineNumber']);
		$por['PoNo'] = barcodeFormatter_PurchaseOrderNumber($por['PoNo']);

		$price = ($por["Price"]*$por["ExchangeRate"])*$por['Quantity']*((100 - intval($por['Discount']))/100);
		$por["Price"] = $price;
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
