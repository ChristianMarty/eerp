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

function purchaseOrderDocumentIngest(stdClass $data, string $category): null|\Error\Data
{
    require_once __DIR__ . "/../../_document.php";

    global $database;
    global $api;

    if(!isset($data->PurchaseOrderNumber)) return \Error\parameterMissing("PurchaseOrderNumber");
    $purchaseOrderNumber = \Numbering\parser(\Numbering\Category::PurchaseOrder, $data->PurchaseOrderNumber);
    if($purchaseOrderNumber == null) return \Error\parameter("PurchaseOrderNumber");

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

    if(count($result) == 0) return \Error\generic("PurchaseOrderNumber not found");

    $po = $result[0];

    $name = \Numbering\format(\Numbering\Category::PurchaseOrder, $po->PurchaseOrderNumber)." ".$po->PurchaseDate;

    $ingestData = new \Document\Ingest\Data();
    $ingestData->ingestName = $data->FileName;
    $ingestData->name = $name;
    $ingestData->category = $category;
    $ingestData->documentDescription = $data->Description??"";
    $ingestData->linkType = \Document\LinkType::Internal;

    $result = \Document\Ingest\save($ingestData);
    if($result instanceof \Error\Data) return $result;

    if($po->DocumentIds === null) $docIds = [];
    else $docIds = explode(",", $po->DocumentIds);
    $docIds[] = $result->documentId;

    if (($key = array_search("", $docIds)) !== false) unset($docIds[$key]); // Remove empty string

    $docIdStr = implode(",",$docIds);

    $updateData = [];
    $updateData['DocumentIds'] = $docIdStr;
    $database->update("purchaseOrder", $updateData,"PurchaseOrderNumber = $purchaseOrderNumber" );

    $api->returnData([$docIdStr]);
}
