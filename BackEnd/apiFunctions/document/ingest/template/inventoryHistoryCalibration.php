<?php
//*************************************************************************************************
// FileName : inventoryHistoryCalibration.php
// FilePath : apiFunctions/document/ingest/template/
// Author   : Christian Marty
// Date		: 11.0..2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../_functions.php";
require_once  __DIR__."/../../../util/_barcodeParser.php";
require_once  __DIR__."/../../../util/_barcodeFormatter.php";

if($api->isPost())
{
    $data = $api->getPostData();

    $date = $database->escape($data->Date);
    $description = $database->escape($data->Description);
    $nextDate = $database->escape($data->NextDate);
    $fileName = $database->escape($data->FileName);

    $invNumber = barcodeParser_InventoryNumber($data->InventoryNumber);
    if($invNumber == 0) $api->returnParameterError("InventoryNumber");

    $query = <<<STR
        SELECT Id, InvNo, Manufacturer, Type, SerialNumber FROM inventory WHERE  InvNo = $invNumber   
    STR;
    $inv = $database->query($query)[0];

    if(!isset($inv->InvNo)) $api->returnError("Inventory number not found");

    $name = $inv->Manufacturer."_".$inv->Type."_".$inv->SerialNumber."_".$date;
	$name = str_replace(" ", "-",$name);
	
	$fileNameIllegalCharactersRegex = '/[ %:"*?<>|\\/]+/';
	$name = preg_replace($fileNameIllegalCharactersRegex, '', $name);

    $ingestData = array();
    $ingestData['FileName'] = $fileName;
    $ingestData['Name'] = $name;
    $ingestData['Type'] = 'Calibration';
    $ingestData['Description'] = $description;

    $result = ingest($ingestData);

	if(!is_int($result)) $api->returnError($result['error']);

    $docIds = array();
    $docIds[] = $result;

    if (($key = array_search("", $docIds)) !== false) unset($docIds[$key]); // Remove empty string

    $docIdStr = implode(",",$docIds);

    $invId = $inv['Id'];
    $userId = $user->userId();
    $query = <<<STR
        INSERT INTO inventory_history (InventoryId, Description, DocumentIds, Date, NextDate, Type, CreationUserId)
        VALUES ($invId,'$description','$docIdStr','$date','$nextDate', 'Calibration', $userId); 
    STR;
    $database->execute($query);

    $api->returnData($docIdStr);
}
