<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/inventory/history/
// Author   : Christian Marty
// Date		: 21.11.2023
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

if($api->isGet())
{
    $parameter = $api->getGetData();
	if(!isset($parameter->InventoryNumber) AND !isset($parameter->EditToken)) $api->returnParameterMissingError("InventoryNumber or EditToken");

    $query = null;
	if(isset($parameter->InventoryNumber))
	{
		$inventoryNumber = barcodeParser_InventoryNumber($parameter->InventoryNumber);
        $query = <<<STR
            SELECT * FROM inventory
            LEFT JOIN inventory_history ON inventory.Id = inventory_history.InventoryId
            WHERE inventory.InventoryNumber = $inventoryNumber
        STR;
	}
	else if(isset($parameter->EditToken))
	{
		$editToken = $database->escape($parameter->EditToken);
        $query = <<<STR
            SELECT * FROM inventory_history
            WHERE EditToken = $editToken
        STR;
	}

    if($query == null) $api->returnError("This error should not happen!");

    $result = $database->query($query);
    if(count($result) === 0) $api->returnEmpty();

    $api->returnData($result[0]);
}
else if($api->isPatch())
{
    $data = $api->getPostData();
    if(!isset($data->EditToken)) $api->returnParameterMissingError("EditToken");

	$token = $database->escape($data->EditToken);
	
	$sqlData = array();
	$sqlData['Type'] = $data->Type;
	$sqlData['Description'] = $data->Description;
	$sqlData['Date'] = $data->Date;
	$sqlData['NextDate'] = $data->NextDate;

    $database->update("inventory_history", $sqlData, "EditToken = $token");

    $api->returnEmpty();
}
else if($api->isPost())
{
	$data = $api->getPostData();

    if(!isset($data->InventoryNumber)) $api->returnParameterMissingError("InventoryNumber");
    $inventoryNumber = barcodeParser_InventoryNumber($data->InventoryNumber);
    if($inventoryNumber == null) $api->returnParameterError("InventoryNumber");

	$sqlData = array();
	$sqlData['Type'] = $data->Type;
	$sqlData['Description'] = $data->Description;
	$sqlData['Date'] = $data->Date;
	$sqlData['NextDate'] = $data->NextDate;
	$sqlData['EditToken']['raw'] = "history_generateEditToken()";
	$sqlData['InventoryId']['raw'] = "(SELECT Id FROM inventory WHERE InventoryNumber = '$inventoryNumber' )";
    $sqlData['CreationUserId'] = $user->userId();
		
	$id = $database->insert("inventory_history", $sqlData);
	
	$query = "SELECT EditToken FROM inventory_history WHERE Id = $id;";

    $api->returnData($database->query($query)[0]);
}
