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
global $user;

require_once __DIR__ . "/_function.php";
require_once __DIR__ . "/../../config.php";

if($api->isGet( \Permission::PurchaseOrder_View))
{
    $parameters = $api->getGetData();

    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError("PurchaseOrderNumber");
    $purchaseOrderNo = \Numbering\parser(\Numbering\Category::PurchaseOrder, $parameters->PurchaseOrderNumber);
    if($purchaseOrderNo == null) $api->returnParameterError("PurchaseOrderNumber");

	$output = getPurchaseOrderData($purchaseOrderNo);
	
	// Get Documents
    $DocIds = $output['MetaData']->DocumentIds ?? null;
    unset($output['MetaData']->DocumentIds);
	
	$output["Documents"] = \Document\getDocumentsFromIds($DocIds);

	$api->returnData($output);
}
else if($api->isPost(\Permission::PurchaseOrder_Create))
{
    $data = $api->getPostData();

    $poCreate = array();
    $poCreate['VendorId'] = intval($data->SupplierId);
    $poCreate['PurchaseDate'] = $data->PurchaseDate;
    if($data->Title != "") $poCreate['Title'] = $data->Title;
    if($data->Description != "") $poCreate['Description'] = $data->Description;
    $poCreate['PurchaseOrderNumber']['raw'] = "purchaseOrder_generatePurchaseOrderNumber()";
    $poCreate['CreationUserId'] = $user->userId();

    $purchaseOrderId = $database->insert("purchaseOrder", $poCreate);

    $query = "SELECT PurchaseOrderNumber FROM purchaseOrder WHERE Id = $purchaseOrderId;";

    $output = [];
    $output["PurchaseOrderNo"] = $database->query($query)[0]->PurchaseOrderNumber;

    $api->returnData($output);
}
else if ($api->isPatch(\Permission::PurchaseOrder_Edit))
{
    $parameters = $api->getGetData();

    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError("PurchaseOrderNumber");
    $purchaseOrderNumber = \Numbering\parser(\Numbering\Category::PurchaseOrder, $parameters->PurchaseOrderNumber);
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

    $database->update("purchaseOrder", $poData, "PurchaseOrderNumber = ".$purchaseOrderNumber);

    $api->returnEmpty();
}