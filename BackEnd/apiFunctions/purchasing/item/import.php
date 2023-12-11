<?php
//*************************************************************************************************
// FileName : import.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../util/_barcodeParser.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->SupplierId)) $api->returnParameterMissingError("SupplierId");
    $supplierId = intval($parameters->SupplierId);
    if($supplierId == 0) $api->returnParameterError("SupplierId");

    if(!isset($parameters->OrderNumber)) $api->returnParameterMissingError("OrderNumber");

    $query = "SELECT * FROM vendor WHERE Id = $supplierId;";

    $supplierData = $database->query($query)[0];

	$orderNumber = $parameters->OrderNumber;

    $name = $supplierData->API;
    if($name === null) $api->returnError( "Supplier not supported!");

    require_once __DIR__ . "/../../externalApi/".$name."/".$name.".php";

    $data = call_user_func($name."_getOrderInformation", $orderNumber);

    $api->returnData($data);
}
else if($api->isPost())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->PurchaseOrderNo)) $api->returnParameterMissingError("PurchaseOrderNo");
    $purchaseOrderNo = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNo);
    if($purchaseOrderNo == null) $api->returnParameterError("PurchaseOrderNo");

    if(!isset($parameters->OrderNumber)) $api->returnParameterMissingError("OrderNumber");
    $orderNumber = $parameters->OrderNumber;

	$data = $api->getPostData();

	$query = "SELECT Id, VendorId FROM purchaseOrder WHERE PoNo = $purchaseOrderNo;";
	
	$result = $database->query($query)[0];
	
	$vendorId = $result->VendorId;
	$id = $result->Id;
	
	$query = "SELECT * FROM vendor WHERE Id = $vendorId;";
    $vendorMetaData = $database->query($query)[0];
	
	$name = $vendorMetaData->API;
    if($name === null) $api->returnError("Supplier not supported!");

    require_once __DIR__ . "/../../externalApi/".$name."/".$name.".php";

    $supplierData = call_user_func($name."_getOrderInformation", $orderNumber);

	$poData = array();
	$poData['OrderNumber'] = $orderNumber;
	$poData['PurchaseDate'] = $supplierData['OrderDate'];
	$poData['CurrencyId']['raw'] = "(SELECT Id FROM finance_currency WHERE CurrencyCode = '".$supplierData['CurrencyCode']."')";
	
	$database->update("purchaseOrder", $poData, "PoNo = ".$purchaseOrderNo);
	
	foreach($supplierData["Lines"] as $line) 
	{
		$sqlData = array();
		$sqlData['LineNo'] = $line['LineNo'];
		$sqlData['Description'] = $line['SupplierDescription'];
		$sqlData['Quantity'] = $line['Quantity'];
		$sqlData['Sku'] = $line['SupplierPartNumber'];
		$sqlData['Price'] = $line['Price'];
		$sqlData['Type'] = 'Part';
		$sqlData['ManufacturerName'] = $line['ManufacturerName'];
		$sqlData['ManufacturerPartNumber'] = $line['ManufacturerPartNumber'];
		$sqlData['OrderReference'] = $line['OrderReference'];
		$sqlData['StockPart']['raw'] = "b'1'";
        $sqlData['VatTaxId'] = $user->vatIdDefault();
        $sqlData['Discount'] = 0;
		
		$sqlData['PurchaseOrderId'] = $id;
		$database->insert("purchaseOrder_itemOrder", $sqlData);
	}
	$output = array();
	$output["PurchaseOrderNo"] = $purchaseOrderNo;
	$api->returnData($output);
}
