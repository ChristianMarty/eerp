<?php
//*************************************************************************************************
// FileName : attachment.php
// FilePath : apiFunctions/document/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_getDocuments.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_barcodeParser.php";

if ($api->isGet())
{
    $parameter = $api->getGetData();
    if(!isset($parameter->Table)) $api->returnParameterMissingError("Table");
    if(!isset($parameter->AttachBarcode)) $api->returnParameterMissingError("AttachBarcode");

    $attachToTable = $parameter->Table;
    $attachToBarcode = $parameter->AttachBarcode;

    $docIdList = null;

    if($attachToTable === "PurchaseOrderDocument")
    {
        $poBarcode =  barcodeParser_PurchaseOrderNumber($attachToBarcode);
        if($poBarcode == null) $api->returnParameterError("AttachBarcode");

        $query = "SELECT DocumentIds FROM purchaseOrder WHERE PoNo = '$poBarcode' LIMIT 1";
        $docIdList = $database->query($query)[0];
    }
    else if($attachToTable === "ManufacturerPartSeriesDocument")
    {
        $manufacturerPartSeriesId =  intval($attachToBarcode);
        if($manufacturerPartSeriesId == null) $api->returnParameterError("AttachBarcode");

        $query = "SELECT DocumentIds FROM manufacturerPart_series WHERE Id = '$manufacturerPartSeriesId' LIMIT 1";
        $docIdList = $database->query($query)[0];
    }
    else if($attachToTable === "ManufacturerPartItemDocument")
    {
        $manufacturerPartItemId = intval($attachToBarcode);
        if($manufacturerPartItemId == null) $api->returnParameterError("AttachBarcode");

        $query = "SELECT DocumentIds FROM manufacturerPart_item WHERE Id = '$manufacturerPartItemId' LIMIT 1";
        $docIdList = $database->query($query)[0];
    }

    $api->returnData(getDocuments($docIdList->DocumentIds));
}
else if($api->isPost())
{
	$data = $api->getPostData();
    $attachToTable = $data->Table;
    $attachToBarcode = $data->AttachBarcode;
	
	$docList = "";
	
	foreach($data->DocumentBarcodes as $key => $line)
	{
        $docNumber = barcodeParser_DocumentNumber($line);
		$docList .= $docNumber.",";
	}
    $docList = substr($docList, 0, -1);


    $docIdList = "";
    if(!empty($docList))
    {
        $query = "SELECT GROUP_CONCAT(Id) AS DocumentIds FROM document WHERE DocumentNumber IN($docList)";
        $docIdList = $database->query($query)[0]->DocumentIds;
    }

    if($attachToTable === "PurchaseOrderDocument")
    {
        $poCode =  barcodeParser_PurchaseOrderNumber($attachToBarcode);
        if($poCode == null) $api->returnParameterError("AttachBarcode");

        $updateData = [];
        $updateData['DocumentIds'] = $docIdList;
        $database->update('purchaseOrder', $updateData, "PoNo = '$poCode'");
    }
    else if($attachToTable === "ManufacturerPartSeriesDocument")
    {
        $manufacturerPartSeriesId =  intval($attachToBarcode);
        if($manufacturerPartSeriesId == null) $api->returnParameterError("AttachBarcode");

        $updateData = [];
        $updateData['DocumentIds'] = $docIdList;
        $database->update('manufacturerPart_series', $updateData, "Id = '$manufacturerPartSeriesId'");
    }
    else if($attachToTable === "ManufacturerPartItemDocument")
    {
        $manufacturerPartItemId =  intval($attachToBarcode);
        if($manufacturerPartItemId == null) $api->returnParameterError("AttachBarcode");

        $updateData = [];
        $updateData['DocumentIds'] = $docIdList;
        $database->update('manufacturerPart_item', $updateData, "Id = '$manufacturerPartItemId'");
    }

    $api->returnEmpty();
}
