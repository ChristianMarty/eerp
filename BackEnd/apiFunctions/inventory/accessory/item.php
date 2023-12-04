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

require_once __DIR__ . "/../../util/_json.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet())
{
    $parameter = $api->getGetData();
    if(!isset($parameter->InventoryNumber)) $api->returnParameterMissingError("InventoryNumber");
    $inventoryNumber = barcodeParser_InventoryNumber($parameter->InventoryNumber);
    if($inventoryNumber == null) $api->returnParameterError('InventoryNumber');

    $accessoryNumber = barcodeParser_InventoryAccessoryNumber($parameter->InventoryNumber);
    if($accessoryNumber == null) $api->returnParameterError('AccessoryNumber');

    $query = <<<STR
        SELECT 
            inventory.InvNo AS InventoryNumber, 
            inventory_accessory.AccessoryNumber, 
            inventory_accessory.Description, 
            inventory_accessory.Note,  
            inventory_accessory.Labeled 
        FROM inventory
        LEFT JOIN inventory_accessory ON inventory_accessory.InventoryId = inventory.Id 
        WHERE inventory.InvNo = $inventoryNumber AND inventory_accessory.AccessoryNumber = $accessoryNumber
        LIMIT 1
    STR;

    $accessory = $database->query($query)[0];

	if($accessory->Labeled == "0") $accessory->Labeled = false;
	else $accessory->Labeled = true;

	$api->returnData($accessory);
}
else if($api->isPatch())
{
	$data = $api->getPostData();
    if(!isset($data->InventoryNumber)) $api->returnParameterMissingError("InventoryNumber");
    $inventoryNumber = barcodeParser_InventoryNumber($data->InventoryNumber);
    if($inventoryNumber == null) $api->returnParameterError('InventoryNumber');

    $accessoryNumber = barcodeParser_InventoryAccessoryNumber($data->InventoryNumber);
    if($accessoryNumber == null) $api->returnParameterError('AccessoryNumber');

    $query = <<<STR
        SELECT 
            Id
        FROM inventory
        WHERE InvNo = $inventoryNumber
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
else if($api->isPost())
{
    $data = $api->getPostData();
    if(!isset($data->InventoryNumber)) $api->returnParameterMissingError("InventoryNumber");
    $inventoryNumber = barcodeParser_InventoryNumber($data->InventoryNumber);

    if(!isset($data->AccessoryNumber)) $api->returnParameterMissingError("AccessoryNumber");
    $accessoryNumber = intval($data->AccessoryNumber);

    $query = <<<STR
        SELECT 
            Id
        FROM inventory
        WHERE InvNo = $inventoryNumber
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

    $database->update("inventory_accessory", $sqlData);

	$api->returnEmpty();
}
