<?php
//*************************************************************************************************
// FileName : track.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet())
{
    $parameter = $api->getGetData();
    if(!isset($parameter->ReceivalId)) $api->returnParameterMissingError("ReceivalId");
    $receivalId = intval($parameter->ReceivalId);

	$output = array();
    $query = <<< STR
        SELECT 
            StockNumber,
            partStock_history.Quantity AS CreateQuantity 
        FROM partStock
        LEFT JOIN partStock_history ON partStock_history.StockId = partStock.Id
        WHERE ReceivalId = $receivalId AND partStock_history.ChangeType = 'Create' AND DeleteRequestUserId IS NULL
    STR;
	
	$result = $database->query($query);
    foreach($result as $r)
	{
        $temp = array();
        $temp['ItemCode'] = barcodeFormatter_StockNumber($r->StockNumber);
        $temp['Type'] = "Part Stock";
        $temp['Description'] = null;
        $temp['CreateQuantity'] = $r->CreateQuantity;
		$output[] = $temp;
	}


    $query = <<< STR
        SELECT InventoryNumber, Title FROM inventory
        LEFT JOIN inventory_purchaseOrderReference ON inventory_purchaseOrderReference.InventoryId = inventory.Id
        WHERE ReceivalId = $receivalId
    STR;

    $result = $database->query($query);
    foreach($result as $r)
    {
        $temp = array();
        $temp['ItemCode'] = barcodeFormatter_InventoryNumber($r->InventoryNumber);
        $temp['Type'] = "Inventory";
        $temp['CreateQuantity'] = null;
        $temp['Description'] = $r->Title;
		$output[] = $temp;
	}

    $api->returnData($output);
}
