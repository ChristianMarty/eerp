<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/purchasing/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/_function.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/_barcodeParser.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError("PurchaseOrderNumber");
    $purchaseOrderNo = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
    if($purchaseOrderNo == null) $api->returnParameterError("PurchaseOrderNumber");

	$output = getPurchaseOrderData($purchaseOrderNo);
	
	// Get Documents
    $DocIds = $output['MetaData']->DocumentIds ?? null;
    unset($output['MetaData']->DocumentIds);
	
	$output["Documents"] = getDocuments($DocIds);

	$api->returnData($output);
}
else if($api->isPost())
{
    $data = $api->getPostData();

    $poCreate = array();
    $poCreate['VendorId'] = intval($data->SupplierId);
    $poCreate['PurchaseDate'] = $data->PurchaseDate;
    if($data->Title != "") $poCreate['Title'] = $data->Title;
    if($data->Description != "") $poCreate['Description'] = $data->Description;

    $poCreate['PoNo']['raw'] = "purchaseOrder_generatePoNo()";

    $purchaseOrderId = $database->insert("purchaseOrder", $poCreate);

    $query = "SELECT PoNo FROM purchaseOrder WHERE Id = $purchaseOrderId;";

    $output = [];
    $output["PurchaseOrderNo"] = $database->query($query)[0]->PoNo;

    $api->returnData($output);
}
else if ($api->isPatch())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError("PurchaseOrderNumber");
    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
    if($purchaseOrderNumber == null) $api->returnParameterError("PurchaseOrderNumber");

    $data = (array)$api->getPostData()->data;

    $poData = array();
    $poData['VendorId'] = intval($data['SupplierId']);
    $poData['Title'] = $data['Title'];
    $poData['PurchaseDate'] = $data['PurchaseDate'];
    $poData['AcknowledgementNumber'] = $data['AcknowledgementNumber'];
    $poData['QuotationNumber'] = $data['QuotationNumber'];
    $poData['OrderNumber'] = $data['OrderNumber'];
    $poData['Description'] = $data['Description'];
    $poData['Carrier'] = $data['Carrier'];
    $poData['PaymentTerms'] = $data['PaymentTerms'];
    $poData['InternationalCommercialTerms'] = $data['InternationalCommercialTerms'];
    $poData['HeadNote'] = $data['HeadNote'];
    $poData['FootNote'] = $data['FootNote'];
    $poData['CurrencyId'] = intval($data['CurrencyId']);
    $poData['ExchangeRate'] = $data['ExchangeRate'];
    $poData['VendorAddressId'] = intval($data['VendorAddressId']);
    $poData['VendorContactId'] = intval($data['VendorContactId']);
    $poData['Status'] = $data['Status'];

    $database->update("purchaseOrder", $poData, "PoNo = ".$purchaseOrderNumber);

    $api->returnEmpty();
}