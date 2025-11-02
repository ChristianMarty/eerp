<?php
//*************************************************************************************************
// FileName : upload.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 11.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../vendor/api/_vendorInterface.php";

if($api->isPost(\Permission::PurchaseOrder_Edit))
{
    $parameters = $api->getGetData();

    if(!isset($parameters->PurchaseOrderNo)) $api->returnParameterMissingError("PurchaseOrderNo");
    $purchaseOrderNo = \Numbering\parser(\Numbering\Category::PurchaseOrder, $parameters->PurchaseOrderNo);
    if($purchaseOrderNo == null) $api->returnParameterError("PurchaseOrderNo");

    $query = <<< QUERY
        SELECT 
            API,
            ApiData
        FROM vendor
        LEFT JOIN purchaseOrder ON purchaseOrder.VendorId = vendor.Id
        WHERE purchaseOrder.PurchaseOrderNumber = '$purchaseOrderNo'
        LIMIT 1;
    QUERY;
    $supplierData = $database->query($query)[0];

    $name = $supplierData->API;

    if($supplierData->ApiData == null) $apiData = null;
    else $apiData = json_decode($supplierData->ApiData);
    require_once  __DIR__ . "/../../externalApi/".$name."/".$name.".php";
    $vendor = new $name($apiData);

    $file = $_FILES["file"]["tmp_name"];
    $data = file_get_contents($file);

    $api->returnData($vendor->parseOrderInformation($data));
}
else if($api->isPatch(\Permission::PurchaseOrder_Edit))
{
    $parameters = $api->getGetData();
    $data = $api->getPostData();

    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError("PurchaseOrderNumber");
    $purchaseOrderNo = \Numbering\parser(\Numbering\Category::PurchaseOrder, $parameters->PurchaseOrderNumber);
    if($purchaseOrderNo == null) $api->returnParameterError("PurchaseOrderNumber");

    $query = "SELECT Id, VendorId FROM purchaseOrder WHERE PurchaseOrderNumber = $purchaseOrderNo;";

    $result = $database->query($query)[0];

    $vendorId = $result->VendorId;
    $purchaseOrderId = $result->Id;
    $currencyCode =  $database->escape($data->CurrencyCode);
    $orderNumber =  $database->escape($data->OrderNumber);
    $orderDate =  $database->escape($data->OrderDate);

    $poData = array();
    $poData['OrderNumber'] = $orderNumber;
    //$poData['PurchaseDate'] = $supplierData['OrderDate'];
    $poData['CurrencyId']['raw'] = "(SELECT Id FROM finance_currency WHERE CurrencyCode = $currencyCode)";

    $database->update("purchaseOrder", $poData, "PurchaseOrderNumber = ".$purchaseOrderNo);

    foreach($data->Lines as $line)
    {
        $sqlData = array();
        $sqlData['LineNumber'] = $line->LineNumber;
        $sqlData['Description'] = $line->SupplierDescription;
        $sqlData['Quantity'] = $line->Quantity;
        $sqlData['Sku'] = $line->SupplierPartNumber;
        $sqlData['Price'] = $line->Price;
        $sqlData['Type'] = 'Part';
        $sqlData['ManufacturerName'] = $line->ManufacturerName;
        $sqlData['ManufacturerPartNumber'] = $line->ManufacturerPartNumber;
        $sqlData['OrderReference'] = $line->OrderReference;
        $sqlData['StockPart']['raw'] = "b'1'";
        $sqlData['VatTaxId'] = $user->vatIdDefault();
        $sqlData['Discount'] = 0;
        $sqlData['CreationUserId'] = $user->userId();

        $sqlData['PurchaseOrderId'] = $purchaseOrderId;
        $database->insert("purchaseOrder_itemOrder", $sqlData);
    }
    $output = array();
    $output["PurchaseOrderNo"] = $purchaseOrderNo;
    $api->returnData($output);
}

