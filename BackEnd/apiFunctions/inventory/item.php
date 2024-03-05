<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/inventory/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../location/_location.php";
require_once __DIR__ . "/../util/_getDocuments.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/getPurchaseInformation.php";

if($api->isGet())
{
	$parameter = $api->getGetData();
	if(!isset($parameter->InventoryNumber) and !isset($parameter->SerialNumber)) $api->returnParameterMissingError("InventoryNumber and SerialNumber");


	if(isset($parameter->InventoryNumber))
	{
		$InvNo = barcodeParser_InventoryNumber($parameter->InventoryNumber);
		if($InvNo == null) $api->returnParameterError('InventoryNumber');
	}
	elseif(isset($parameter->SerialNumber))
	{
		$SerNo = $database->escape($parameter->SerialNumber);
	}

	$baseQuery = <<<STR
		SELECT 
		    inventory.Id AS Id, 
		    PicturePath, 
		    InventoryNumber, 
		    inventory.Title, 
		    inventory.Manufacturer AS ManufacturerName, 
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
			vendor_displayName(vendor.Id) AS SupplierName, 
			HomeLocationId, 
			location.LocationNumber AS LocationNumber, 
			InventoryCategoryId AS CategoryId, 
			inventory.LocationId 
		FROM `inventory`
		LEFT JOIN `vendor` On vendor.Id = inventory.VendorId 
		LEFT JOIN `location` On location.Id = inventory.LocationId 
		LEFT JOIN `inventory_category` On inventory_category.Id = inventory.InventoryCategoryId 
	STR;

	if(isset($InvNo)) $baseQuery .=" WHERE `InventoryNumber` = $InvNo";
	if(isset($SerNo)) $baseQuery .=" WHERE `SerialNumber` = $SerNo";
	
	global $dataRootPath;
	global $picturePath;

	$pictureRootPath = $dataRootPath.$picturePath."/";

	$output = $database->query($baseQuery)[0];

	$id = $output->Id;

	$output->PicturePath = $pictureRootPath.$output->PicturePath;
	$output->InventoryNumber = $output->InventoryNumber;
	$output->InventoryBarcode = barcodeFormatter_InventoryNumber($output->InventoryNumber);

	$location = new Location();
	$output->LocationName = $location->name($output->LocationId);
	$output->LocationPath = $location->path($output->LocationId);
	$output->HomeLocationName = $location->name($output->HomeLocationId);
	$output->HomeLocationPath = $location->path($output->HomeLocationId);

	// Get Purchase Information
	$query = <<<STR
		SELECT  
		    PurchaseOrderNumber, 
		    purchaseOrder_itemOrder.LineNumber AS LineNumber , 
		    purchaseOrder_itemOrder.Description, 
		    purchaseOrder_itemOrder.Discount, 
		    vendor_displayName(vendor.Id) AS SupplierName, 
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
		WHERE inventory_purchaseOrderReference.InventoryId = $id
		ORDER BY PurchaseDate
	STR;
	$purchase = $database->query($query);

	$totalPurchase = 0;
	$totalMaintenance = 0;
	foreach ($purchase as $r)
	{
		$r->PurchaseOrderBarcode = barcodeFormatter_PurchaseOrderNumber($r->PurchaseOrderNumber, $r->LineNumber);
		$r->PurchaseOrderNumber = barcodeFormatter_PurchaseOrderNumber($r->PurchaseOrderNumber);

		$price = ($r->Price*$r->ExchangeRate)*$r->Quantity*((100 - intval($r->Discount))/100);
		$r->Price = $price;
		if($r->CostType == 'Purchase')  $totalPurchase += $price;
		else $totalMaintenance += $price;
	}
	
	if(count($purchase) == 0) // Fallback to legacy data
	{
		$row = [];
		$row["PurchaseOrderNumber"] = null;
		$row["LineNumber"] = null;
		$row["Price"] = $output->PurchasePrice;
		$row["Currency"] = "CHF"; // TODO: Fix this
		$row["SupplierPartNumber"] = null;
		$row["SupplierName"] = $output->SupplierName;
		$row["SupplierId"] = null;
		$row["PurchaseDate"] = $output->PurchaseDate;
		$row["OrderReference"] = null;
		$row["VendorId"] = 0;
		$row["Quantity"] = 1;
		$row["CostType"] = 'Legacy Purchase';
		$row["Description"] = "";
		
		$totalPrice = $row["Price"];
		
		$purchase[] = $row;
	}
	
	$output->PurchaseInformation = $purchase;

	$output->TotalPurchaseCost =  round($totalPurchase, 2);
	$output->TotalMaintenanceCost =  round($totalMaintenance, 2);
	$output->TotalCostOfOwnership =  round($totalPurchase+$totalMaintenance, 2);
	$output->TotalCurrency = "CHF"; //TODO: Fix  $purchase[0]["Currency"];
	
	// Get Accessory
	$query = <<<STR
		SELECT  
		    AccessoryNumber, 
		    Description,
		    Note,
		    Labeled
		FROM inventory_accessory
		WHERE InventoryId = $id
		ORDER BY AccessoryNumber ASC
	STR;

	$accessory = $database->query($query);
	foreach ($accessory as $r)
	{
		$r->AccessoryBarcode = $output->InventoryBarcode."-".$r->AccessoryNumber;
		if($r->Labeled == "0") $r->Labeled = false;
		else $r->Labeled = true;
	}
	
	$output->Accessory = $accessory;
	
	// Get Documents
	$output->Documents = getDocumentsFromIds($output->DocumentIds ?? null);
	unset($output->DocumentIds);
	
	// Get History
	$query = <<<STR
		SELECT  
		    *
		FROM inventory_history
		WHERE InventoryId = $id
		ORDER BY Date ASC
	STR;
	$history = $database->query($query);

	global $documentRootPath;
	foreach ($history as $r)
	{
		$documents = array();

		if(isset($r->DocumentIds)) $DocIds = explode(",",$r->DocumentIds);
		else $DocIds = null;
		
		if(!empty($DocIds))
		{
			$baseQuery = "SELECT * FROM `document` WHERE Id IN(".implode(", ",$DocIds).")";
			$documents = $database->query($baseQuery);
			foreach ($documents as $doc)
			{
				$doc->Path = $documentRootPath."/".$doc->Type."/".$doc->Path;
			}
		}
		$r->Documents = $documents;
		
		unset($r->DocumentIds);
		unset($r->Id);
		unset($r->InventoryId);
	}
	
	$output->History = $history;
	
	$api->returnData($output);
}
else if($api->isPost())
{
	$data = $api->getPostData();

	$sqlData = array();
	$sqlData['InventoryNumber']['raw'] = "(SELECT generateItemNumber())";
	$sqlData['Title'] = $data->Title;
	$sqlData['Manufacturer'] = $data->ManufacturerName;
	$sqlData['Type'] = $data->Type;
	$sqlData['SerialNumber'] = $data->SerialNumber;
	$sqlData['LocationId']['raw'] = "(SELECT Id FROM location WHERE LocationNumber = ".$database->escape($data->LocationNumber).")";
	$sqlData['InventoryCategoryId'] = intval($data->CategoryId);

	$Id = $database->insert("inventory", $sqlData);

    $query = " SELECT `InventoryNumber` FROM `inventory` WHERE `Id` = $Id;";

	$output = [];
	$output['InventoryNumber'] = $database->query($query)[0]->InventoryNumber;
	$output['InventoryBarcode'] = barcodeFormatter_InventoryNumber($output['InventoryNumber']);

	$api->returnData($output);
}
