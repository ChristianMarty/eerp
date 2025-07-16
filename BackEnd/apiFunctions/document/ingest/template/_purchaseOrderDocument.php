<?php
//*************************************************************************************************
// FileName : _purchaseOrderDocument.php
// FilePath : apiFunctions/document/ingest/template/
// Author   : Christian Marty
// Date		: 03.12.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function purchaseOrderDocumentIngest(stdClass $data, string $type): void
{
    require_once __DIR__ . "/../../_document.php";
    require_once __DIR__."/../../../util/_barcodeParser.php";
    require_once __DIR__."/../../../util/_barcodeFormatter.php";

    global $database;
    global $api;

    if(!isset($data->PurchaseOrderNumber)) $api->returnParameterMissingError("PurchaseOrderNumber");
    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($data->PurchaseOrderNumber);
    if($purchaseOrderNumber == null) $api->returnParameterError("PurchaseOrderNumber");

    $query = <<<STR
        SELECT 
            Id, 
            PurchaseOrderNumber, 
            PurchaseDate, 
            DocumentIds 
        FROM purchaseOrder 
        WHERE  PurchaseOrderNumber = $purchaseOrderNumber
        LIMIT 1
    STR;
    $result = $database->query($query);

    if(count($result)== 0) $api->returnError("PurchaseOrderNumber not found");

    $po = $result[0];

    $name= barcodeFormatter_PurchaseOrderNumber($po->PurchaseOrderNumber)."_".$po->PurchaseDate;

    $ingestData = array();
    $ingestData['FileName'] = $data->FileName;
    $ingestData['Name'] = $name;
    $ingestData['Type'] = $type;
    $ingestData['Description'] = $type.' '.date('Y-m-d');
    $ingestData['Note'] = $data->Note;

    $result = ingest($ingestData);

    if(!is_int($result)) $api->returnError($result['error']);

    if($po->DocumentIds === null) $docIds = [];
    else $docIds = explode(",", $po->DocumentIds);
    $docIds[] = $result;

    if (($key = array_search("", $docIds)) !== false) unset($docIds[$key]); // Remove empty string

    $docIdStr = implode(",",$docIds);

    $updateData = [];
    $updateData['DocumentIds'] = $docIdStr;
    $database->update("purchaseOrder",$updateData,"PurchaseOrderNumber = $purchaseOrderNumber" );

    $api->returnData([$docIdStr]);
}
