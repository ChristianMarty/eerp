<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/metrology/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

	if(!isset($parameter->TestSystemNumber)) $api->returnParameterMissingError("TestSystemNumber");
    $testSystemNumber = barcodeParser_TestSystemNumber($parameter->TestSystemNumber);
    if($testSystemNumber == null) $api->returnParameterError("TestSystemNumber");

	$testDate = null;
	if(isset($parameter->TestDate)) $testDate = $database->escape($parameter->TestDate);

    $query = <<<STR
        SELECT 
            * 
        FROM testSystem
        WHERE TestSystemNumber = '$testSystemNumber' 
        LIMIT 1
    STR;

    $output = $database->query($query)[0];

	$output->TestSystemBarcode = barcodeFormatter_TestSystemNumber($output->TestSystemNumber);

    $testSystemId = $output->Id;
    $query = <<<STR
        SELECT 
            inventory.InvNo AS InventoryNumber, 
            inventory.Title, 
            inventory.Manufacturer, 
            inventory.SerialNumber, 
            inventory.Type, 
            testSystem_item.Usage, 
            testSystem_item.CalibrationRequired, 
            inventory_history.Date, 
            inventory_history.NextDate  
        FROM testSystem_item
        LEFT JOIN inventory ON inventory.Id = testSystem_item.InventoryId
        LEFT JOIN inventory_history ON inventory_history.Id = (
            SELECT Id FROM inventory_history 
            WHERE TYPE = 'Calibration' AND InventoryId = inventory.Id AND Date <= $testDate ORDER BY Date DESC LIMIT 1
        ) 
        WHERE testSystem_item.TestSystemId = $testSystemId
    STR;
	$result = $database->query($query);

    $output->Item = array();
	foreach ($result as $r)
	{
        $r->InventoryBarcode = barcodeFormatter_InventoryNumber($r->InventoryNumber);

		if($r->CalibrationRequired == 0)
		{
            $r->CalibrationRequired = false;
			$r->CalibrationDate = "N/A";
			$r->NextCalibrationDate = "N/A";
		}
		else 
		{
            $r->CalibrationRequired = true;
			$r->CalibrationDate = $r->Date;
			$r->NextCalibrationDate = $r->NextDate;
		}
		
		unset($r->Date);
		unset($r->NextDate);
		
		$output->Item[] = $r;
	}
	
	$api->returnData($output);
}
else if($api->isPost("metrology.create"))
{
    $data = $api->getPostData();
    if(!isset($data->Name)) $api->returnParameterMissingError("Name");
    if(empty($data->Name)) $api->returnParameterError("Name");

    $sqlData = array();
    $sqlData['Name'] = $data->Name;
    $sqlData['Description']  = $data->Description;
    $sqlData['TestSystemNumber']['raw'] = "(SELECT generateItemNumber())";
    $id = $database->insert("testSystem", $sqlData);

    $query ="SELECT TestSystemNumber AS Number  FROM testSystem WHERE Id = $id;";
    $output = [];
    $output['TestSystemBarcode'] = barcodeFormatter_TestSystemNumber($database->query($query)[0]->Number);
    $api->returnData($output);
}
