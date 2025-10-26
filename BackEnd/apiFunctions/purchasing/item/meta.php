<?php
//*************************************************************************************************
// FileName : meta.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 20.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../_function.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet(\Permission::PurchaseOrder_View))
{
    $parameters = $api->getGetData();

    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError("PurchaseOrderNumber");
    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
    if($purchaseOrderNumber == 0) $api->returnParameterError("PurchaseOrderNumber");

    $query = <<<STR
        SELECT 
            Carrier, 
            PaymentTerms, 
            InternationalCommercialTerms, 
            HeadNote, 
            FootNote, 
            VendorContactId, 
            VendorAddressId, 
            ShippingContactId, 
            BillingContactId, 
            PurchaseContactId, 
            purchaseOrder.DocumentIds, 
            purchaseOrder.PurchaseOrderNumber AS PurchaseOrderNumber, 
            purchaseOrder.CreationDate, 
            purchaseOrder.PurchaseDate, 
            purchaseOrder.Title, 
            purchaseOrder.Description, 
            purchaseOrder.Status,
            vendor_displayName(vendor.Id) AS SupplierName, 
            vendor.Id AS SupplierId, 
            AcknowledgementNumber, 
            OrderNumber, 
            finance_currency.CurrencyCode, 
            finance_currency.Digits AS CurrencyDigits,  
            finance_currency.Id AS CurrencyId, 
            ExchangeRate, 
            purchaseOrder.QuotationNumber 
        FROM purchaseOrder
        LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId 
        LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId
        WHERE purchaseOrder.PurchaseOrderNumber = '$purchaseOrderNumber'
        LIMIT 1
    STR;

    $output = $database->query($query)[0];

    $output->PurchaseOrderBarcode = barcodeFormatter_PurchaseOrderNumber($output->PurchaseOrderNumber);

    $api->returnData($output);

}
else if ($api->isPatch(\Permission::PurchaseOrder_Edit))
{
    $parameters = $api->getGetData();

    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError("PurchaseOrderNumber");
    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
    if($purchaseOrderNumber == 0) $api->returnParameterError("PurchaseOrderNumber");

    $data = $api->getPostData();

    $poData = array();
    $poData['VendorId'] = intval($data->SupplierId);
    $poData['Title'] = $data->Title;
    $poData['PurchaseDate'] = $data->PurchaseDate;
    $poData['AcknowledgementNumber'] = $data->AcknowledgementNumber;
    $poData['QuotationNumber'] = $data->QuotationNumber;
    $poData['OrderNumber'] = $data->OrderNumber;
    $poData['Description'] = $data->Description;
    $poData['Carrier'] = $data->Carrier;
    $poData['PaymentTerms'] = $data->PaymentTerms;
    $poData['InternationalCommercialTerms'] = $data->InternationalCommercialTerms;
    $poData['HeadNote'] = $data->HeadNote;
    $poData['FootNote'] = $data->FootNote;
    $poData['CurrencyId'] = intval($data->CurrencyId);
    $poData['ExchangeRate'] = $data->ExchangeRate;
    $poData['VendorAddressId'] = intval($data->VendorAddressId);
    $poData['VendorContactId'] = intval($data->VendorContactId);
    $poData['Status'] = $data->Status;

    $database->update( "purchaseOrder", $poData, "PurchaseOrderNumber = ".$purchaseOrderNumber);

    $api->returnEmpty();
}
