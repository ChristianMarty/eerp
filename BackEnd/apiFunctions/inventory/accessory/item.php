<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/inventory/accessory/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../util/_json.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet(\Permission::Inventory_Accessory_View))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->ItemCode)) $api->returnParameterMissingError("ItemCode");
    $inventoryNumber = barcodeParser_InventoryNumber($parameter->ItemCode);
    if($inventoryNumber == null) $api->returnParameterError('InventoryItemCode');

    $accessoryNumber = barcodeParser_InventoryAccessoryNumber($parameter->ItemCode);
    if($accessoryNumber == null) $api->returnParameterError('AccessoryItemCode');

    $query = <<<STR
        SELECT 
            InventoryNumber, 
            inventory_accessory.AccessoryNumber, 
            inventory_accessory.Description, 
            inventory_accessory.Note,  
            inventory_accessory.Labeled 
        FROM inventory
        LEFT JOIN inventory_accessory ON inventory_accessory.InventoryId = inventory.Id 
        WHERE inventory.InventoryNumber = $inventoryNumber AND inventory_accessory.AccessoryNumber = $accessoryNumber
        LIMIT 1
    STR;

    $accessory = $database->query($query)[0];

	if($accessory->Labeled == "0") $accessory->Labeled = false;
	else $accessory->Labeled = true;

    $accessory->ItemCode = barcodeFormatter_InventoryNumber($accessory->InventoryNumber, $accessory->AccessoryNumber);
    $accessory->InventoryNumber = intval($accessory->InventoryNumber);
    $accessory->AccessoryNumber = intval($accessory->AccessoryNumber);

	$api->returnData($accessory);
}
else if($api->isPatch(\Permission::Inventory_Accessory_Edit))
{
	$data = $api->getPostData();
    if(!isset($data->ItemCode)) $api->returnParameterMissingError("ItemCode");
    $inventoryNumber = barcodeParser_InventoryNumber($data->ItemCode);
    if($inventoryNumber == null) $api->returnParameterError('InventoryItemCode');

    $accessoryNumber = barcodeParser_InventoryAccessoryNumber($data->ItemCode);
    if($accessoryNumber == null) $api->returnParameterError('AccessoryItemCode');

    $query = <<<STR
        SELECT 
            Id
        FROM inventory
        WHERE InventoryNumber = $inventoryNumber
    STR;
    $inventoryId = $database->query($query)[0]->Id;

	$sqlData = array();
	$sqlData['AccessoryNumber'] = $accessoryNumber;
	$sqlData['Description'] = $data->Description;
	$sqlData['Note'] = $data->Note;
    $sqlData['Labeled'] = $data->Labeled;

	$database->update("inventory_accessory", $sqlData, "InventoryId = $inventoryId AND AccessoryNumber = $accessoryNumber");

    $api->returnEmpty();
}
else if($api->isPost(\Permission::Inventory_Accessory_Create))
{
    $data = $api->getPostData();
    if(!isset($data->ItemCode)) $api->returnParameterMissingError("ItemCode");
    $inventoryNumber = barcodeParser_InventoryNumber($data->ItemCode);

    $query = <<<STR
        SELECT 
            Id
        FROM inventory
        WHERE InventoryNumber = $inventoryNumber
    STR;
    $inventoryId = $database->query($query)[0]->Id;

    $query = <<<STR
        SELECT 
            AccessoryNumber+1 AS NextAccessoryNumber
        FROM inventory_accessory
        WHERE InventoryId = $inventoryId
        ORDER BY AccessoryNumber 
        DESC LIMIT 1
    STR;
    $resultData = $database->query($query)[0];

    $nextAccessoryNumber = 1; // Value for first Item
    if(isset($resultData->NextAccessoryNumber)) $nextAccessoryNumber = $resultData->NextAccessoryNumber;
	
	$sqlData = array();
	$sqlData['InventoryId'] = $inventoryId;
	$sqlData['AccessoryNumber'] = $nextAccessoryNumber;
	$sqlData['Description'] = $data->Description;
	$sqlData['Note'] = $data->Note;
    $sqlData['Labeled'] = $data->Labeled;
    $sqlData['CreationUserId'] = $user->userId();

    $database->insert("inventory_accessory", $sqlData);

	$api->returnEmpty();
}
