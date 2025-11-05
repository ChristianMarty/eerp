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
global $user;

require_once __DIR__ . "/../location/_location.php";

require_once __DIR__ . "/../document/_document.php";

if($api->isGet(\Permission::Inventory_View))
{
	$parameter = $api->getGetData();
	if(!isset($parameter->InventoryNumber) and !isset($parameter->SerialNumber)) $api->returnParameterMissingError("InventoryNumber and SerialNumber");

	if(isset($parameter->InventoryNumber)) {
		$InvNo = \Numbering\parser(\Numbering\Category::Inventory, $parameter->InventoryNumber);
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
            inventory_category.Name as CategoryName,
            inventory.LocationId 
        FROM inventory
        LEFT JOIN vendor On vendor.Id = inventory.VendorId 
        LEFT JOIN inventory_category ON inventory_category.Id = inventory.InventoryCategoryId
    STR;

	if(isset($InvNo)) $baseQuery .=" WHERE `InventoryNumber` = $InvNo";
	if(isset($SerNo)) $baseQuery .=" WHERE `SerialNumber` = $SerNo";
	
	global $dataRootPath;
	global $picturePath;

	$pictureRootPath = $dataRootPath.$picturePath."/";
	$output = $database->query($baseQuery)[0];

	$id = $output->Id;
    unset($output->Id);

	$output->PicturePath = $pictureRootPath.$output->PicturePath;
	$output->InventoryNumber = intval($output->InventoryNumber);
	$output->ItemCode = \Numbering\format(\Numbering\Category::Inventory, $output->InventoryNumber);
    if($output->SerialNumber === null) $output->SerialNumber = "";

    // Add Location
    $location = new Location();
    $output->Location = $location->locationItem($output->LocationId, $output->HomeLocationId);
    unset($output->LocationId);
    unset($output->HomeLocationId);

    // Add Attributes
    $attributes = [];
    $macWired = ["Name"=>"Mac Address Wired", "Value"=>$output->MacAddressWired??""];
    unset($output->MacAddressWired);
    $attributes[] = $macWired;
    $macWireless = ["Name"=>"Mac Address Wireless", "Value"=>$output->MacAddressWireless??""];
    unset($output->MacAddressWireless);
    $attributes[] = $macWireless;
    $output->Attribute = $attributes;

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
		$r->PurchaseOrderBarcode = \Numbering\format(\Numbering\Category::PurchaseOrder, $r->PurchaseOrderNumber, $r->LineNumber);
		$r->PurchaseOrderNumber = \Numbering\format(\Numbering\Category::PurchaseOrder, $r->PurchaseOrderNumber);

		$price = $r->Price*$r->Quantity*((100 - intval($r->Discount))/100);
		$r->Price = $price;
		if($r->CostType == 'Purchase')  $totalPurchase += ($price*$r->ExchangeRate);
		else $totalMaintenance += ($price*$r->ExchangeRate);
	}
	
	if($output->PurchasePrice !== NULL) // Fallback to legacy data
	{
		$row = [];
		$row["PurchaseOrderNumber"] = null;
		$row["LineNumber"] = null;
		$row["Price"] = floatval($output->PurchasePrice);
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

        $totalPurchase += $row["Price"];
		
		$purchase[] = $row;
	}
    unset($output->PurchasePrice);
    unset($output->SupplierName);
    unset($output->PurchaseDate);
	
	$output->PurchaseInformation = new stdClass();
    $output->PurchaseInformation->Item = $purchase;

    $total = new stdClass();
    $total->PurchaseCost =  round($totalPurchase, 2);
    $total->MaintenanceCost =  round($totalMaintenance, 2);
    $total->CostOfOwnership =  round($totalPurchase+$totalMaintenance, 2);
    $total->Currency = "CHF"; //TODO: Fix  $purchase[0]["Currency"];

	$output->PurchaseInformation->Total = $total;
	
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
		$r->ItemCode = \Numbering\format(\Numbering\Category::Inventory, $output->InventoryNumber, $r->AccessoryNumber);
		if($r->Labeled == "0") $r->Labeled = false;
		else $r->Labeled = true;
	}
	
	$output->Accessory = $accessory;
	
	// Get Documents
	$output->Documents = \Document\getDocumentsFromIds($output->DocumentIds ?? null);
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
        $r->Documents = \Document\getDocumentsFromIds($r->DocumentIds ?? null);
		unset($r->DocumentIds);
		unset($r->Id);
		unset($r->InventoryId);
	}
	
	$output->History = $history;
	
	$api->returnData($output);
}
else if($api->isPost(\Permission::Inventory_Create))
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
    $sqlData['CreationUserId'] = $user->userId();

	$Id = $database->insert("inventory", $sqlData);

    $query = " SELECT `InventoryNumber` FROM `inventory` WHERE `Id` = $Id;";

	$output = [];
	$output['InventoryNumber'] = $database->query($query)[0]->InventoryNumber;
	$output['ItemCode'] = \Numbering\format(\Numbering\Category::Inventory, $output['InventoryNumber']);

	$api->returnData($output);
}
