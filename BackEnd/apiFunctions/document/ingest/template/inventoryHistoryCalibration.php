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

require_once __DIR__ . "/../../_document.php";

if($api->isPost(Permission::Document_Ingest_Save))
{
    $data = $api->getPostData();

    $invNumber = \Numbering\parser(\Numbering\Category::Inventory, $data->InventoryNumber);
    if($invNumber == 0) $api->returnParameterError("InventoryNumber");

    $query = <<<STR
        SELECT Id, InventoryNumber, Manufacturer, Type, SerialNumber FROM inventory WHERE  InventoryNumber = $invNumber   
    STR;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $data->InventoryNumber);
    $inv = $result[0];

    $name = $inv->Manufacturer."_".$inv->Type."_".$inv->SerialNumber."_".$data->Date;
	$name = str_replace(" ", "-",$name);
	
	$fileNameIllegalCharactersRegex = '/[ %:"*?<>|\\/]+/';
	$name = preg_replace($fileNameIllegalCharactersRegex, '', $name);

    $ingestData = new \Document\Ingest\Data();
    $ingestData->ingestName = $data->FileName;
    $ingestData->name = $name;
    $ingestData->category = 'Calibration';
    $ingestData->documentDescription = $data->Description??"";
    $ingestData->linkType = \Document\documentLinkType($data->LinkType);

    $result = \Document\Ingest\save($ingestData);
    if($result instanceof \Error\Data) return $result;

    if(empty($data->NextDate)) $data->NextDate = null;

    $createHistory = array();
    $createHistory['InventoryId'] = intval($inv->Id);
    $createHistory['Description'] = $data->Description;
    $createHistory['DocumentIds'] = $result->documentId;
    $createHistory['Date'] = $data->Date;
    $createHistory['NextDate'] = $data->NextDate;
    $createHistory['Type'] = 'Calibration';
    $createHistory['CreationUserId'] = $user->userId();

    $database->insert("inventory_history", $createHistory);


    $api->returnData($result->documentId);
}
