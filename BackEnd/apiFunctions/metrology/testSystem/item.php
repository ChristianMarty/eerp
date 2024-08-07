<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/metrology/testSystem/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

	if(!isset($parameter->TestSystemNumber)) $api->returnParameterMissingError("TestSystemNumber");
    $testSystemNumber = barcodeParser_TestSystemNumber($parameter->TestSystemNumber);
    if($testSystemNumber == null) $api->returnParameterError("TestSystemNumber");

    $query = <<<STR
        SELECT 
            Id,
            TestSystemNumber,
            Name,
            Description
        FROM testSystem
        WHERE TestSystemNumber = '$testSystemNumber' 
        LIMIT 1
    STR;

    $output = $database->query($query);

    if(count($output) == 0){
        $api->returnError("Item not found");
    }
    $output = $output[0];

	$output->ItemCode = barcodeFormatter_TestSystemNumber($output->TestSystemNumber);
    $output->TestSystemNumber = intval($output->TestSystemNumber);
    $testSystemId = $output->Id;
    unset($output->Id);

    $query = <<<STR
        SELECT 
            testSystem_instrument.Id AS InstrumentId, 
            testSystem_instrument.Name, 
            testSystem_instrument.Description, 
            
            inventory.InventoryNumber,
            inventory.Title,
            inventory.Manufacturer AS ManufacturerName,
            inventory.SerialNumber,
            inventory.Type,
            
            testSystem_instrument_history.AddedDate, 
            testSystem_instrument_history.RemovedDate,
            
            inventory_history.Date AS CalibrationDate, 
            inventory_history.NextDate AS CalibrationExpirationDate 
                    
        FROM testSystem_instrument 
        LEFT JOIN testSystem_instrument_history ON testSystem_instrument.Id = testSystem_instrument_history.InstrumentId 
        LEFT JOIN inventory ON inventory.Id = testSystem_instrument_history.InventoryId
        LEFT JOIN inventory_history ON inventory_history.Id = ( 
            SELECT Id FROM inventory_history WHERE TYPE = 'Calibration' AND InventoryId = inventory.Id AND Date <= testSystem_instrument_history.AddedDate ORDER BY Date DESC LIMIT 1 
        )
        WHERE testSystem_instrument.TestSystemId = $testSystemId
    STR;

	$result = $database->query($query);

    $output->Item = [];
	foreach ($result as $r)
	{
        if(!key_exists($r->InstrumentId,$output->Item)) {
            $instrument = [];
            $instrument['Name'] = $r->Name;
            $instrument['Description'] = $r->Description;
            $instrument['Equipment']  = [];

            $output->Item[$r->InstrumentId] = $instrument;
        }

        $equipment = [];
        $equipment['InventoryNumber'] = intval($r->InventoryNumber);
        $equipment['ItemCode'] = barcodeFormatter_InventoryNumber($r->InventoryNumber);
        $equipment['Title'] = $r->Title;
        $equipment['ManufacturerName'] = $r->ManufacturerName;
        $equipment['SerialNumber'] = $r->SerialNumber;
        $equipment['Type'] = $r->Type;
        $equipment['AddedDate'] = $r->AddedDate;
        $equipment['RemovedDate'] = $r->RemovedDate;
        $equipment['CalibrationDate'] = $r->CalibrationDate;
        $equipment['CalibrationExpirationDate'] = $r->CalibrationExpirationDate;

        $output->Item[$r->InstrumentId]['Equipment'][] = $equipment;
	}

    $output->Item = array_values($output->Item);
	
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

    $query ="SELECT TestSystemNumber AS Number FROM testSystem WHERE Id = $id;";
    $output = [];
    $output['TestSystemBarcode'] = barcodeFormatter_TestSystemNumber($database->query($query)[0]->Number);
    $api->returnData($output);
}
