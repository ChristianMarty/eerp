<?php
//*************************************************************************************************
// FileName : inventoryHistoryCalibration.php
// FilePath : apiFunctions/document/ingest/template/
// Author   : Christian Marty
// Date		: 11.0..2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";
require_once __DIR__ . "/../../../../config.php";

require_once __DIR__ . "/../../_functions.php";
require_once  __DIR__."/../../../util/_barcodeParser.php";
require_once  __DIR__."/../../../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents('php://input'),true);

    $dbLink = dbConnect();

    $invNumber = dbEscapeString($dbLink, $data['InventoryNumber']);
    $date = dbEscapeString($dbLink, $data['Date']);
    $description = dbEscapeString($dbLink, $data['Description']);
    $nextDate = dbEscapeString($dbLink, $data['NextDate']);
    
    $fileName = dbEscapeString($dbLink, $data['FileName']);

    $invNumber = barcodeParser_InventoryNumber($invNumber);
    
    if(!$invNumber) sendResponse(null,"Inventory number invalid");

    $query = <<<STR
        SELECT Id, InvNo, Manufacturer, Type, SerialNumber FROM inventory WHERE  InvNo = $invNumber   
    STR;

    $result = dbRunQuery($dbLink,$query);
    if(!$result)
    {
        dbClose($dbLink);
        sendResponse(null,"DB Error");
    }
    
    $inv = mysqli_fetch_assoc($result);

    if(!isset($inv['InvNo']))
    {
        dbClose($dbLink);
        sendResponse(null,"Inventory number not found");
    }

    $name = $inv['Manufacturer']."_".$inv['Type']."_".$inv['SerialNumber']."_".$date;
	
	$name = str_replace(" ", "-",$name);
	
	$fileNameIllegalCharactersRegex = '/[ %:"*?<>|\\/]+/';
	$name = preg_replace($fileNameIllegalCharactersRegex, '', $name);

    dbClose($dbLink);

    $ingestData = array();
    $ingestData['FileName'] = $fileName;
    $ingestData['Name'] = $name;
    $ingestData['Type'] = 'Calibration';
    $ingestData['Description'] = $description;

    $result = ingest($ingestData);

	if(!is_int($result)) sendResponse(null,$result['error']);

    $docIds = array();
    $docIds[] = $result;

    if (($key = array_search("", $docIds)) !== false) unset($docIds[$key]); // Remove empty string

    $docIdStr = implode(",",$docIds);

    $invId = $inv['Id'];
    $query = <<<STR
        INSERT INTO inventory_history (InventoryId, Description, DocumentIds, Date, NextDate, Type)
        VALUES ($invId,'$description','$docIdStr','$date','$nextDate', 'Calibration'); 
    STR;

    $dbLink = dbConnect();
    $result = dbRunQuery($dbLink,$query);
    dbClose($dbLink);

    sendResponse($docIdStr,null);
}

?>

