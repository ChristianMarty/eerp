<?php
//*************************************************************************************************
// FileName : meta.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 20.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../_function.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_GET["PurchaseOrderNumber"])) sendResponse(NULL, "Purchase Order Number Undefined");
    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($_GET["PurchaseOrderNumber"]);
    if(!$purchaseOrderNumber) sendResponse(NULL, "Purchase Order Number Parser Error");

    $query = <<<STR
        SELECT Carrier, PaymentTerms, InternationalCommercialTerms, HeadNote, FootNote, 
            VendorContactId, VendorAddressId, ShippingContactId, BillingContactId, PurchaseContactId, 
            purchaseOrder.DocumentIds, purchaseOrder.PoNo, purchaseOrder.CreationDate, 
            purchaseOrder.PurchaseDate, purchaseOrder.Title, purchaseOrder.Description, 
            purchaseOrder.Status ,vendor.Name AS SupplierName, 
            vendor.Id AS SupplierId, AcknowledgementNumber, OrderNumber, 
            finance_currency.CurrencyCode, finance_currency.Digits AS CurrencyDigits,  finance_currency.Id AS CurrencyId, 
            ExchangeRate, purchaseOrder.QuotationNumber FROM purchaseOrder
        LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId 
        LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId
        WHERE purchaseOrder.PoNo = '$purchaseOrderNumber'
    STR;

    $dbLink = dbConnect();

    $result = dbRunQuery($dbLink,$query);

    $output = mysqli_fetch_assoc($result);

    $output['PurchaseOrderNumber'] = $output['PoNo'];
    $output['PurchaseOrderBarcode'] = barcodeFormatter_PurchaseOrderNumber($output['PoNo']);
    $output['CurrencyId'] = intval($output['CurrencyId']);
    $output['VendorContactId'] = intval($output['VendorContactId']);
    $output['VendorAddressId'] = intval($output['VendorAddressId']);
    $output['ShippingContactId'] = intval($output['ShippingContactId']);
    $output['BillingContactId'] = intval($output['BillingContactId']);
    $output['PurchaseContactId'] = intval($output['PurchaseContactId']);
    $output['SupplierId'] = intval($output['SupplierId']);
    $output['CurrencyDigits'] = intval($output['CurrencyDigits']);
    $output['CurrencyDigits'] = floatval($output['ExchangeRate']);

    unset($output['PoNo']);

    dbClose($dbLink);

    sendResponse($output);

}
else if ($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
    if(!isset($_GET["PurchaseOrderNumber"])) sendResponse(NULL, "Purchase Order Number Undefined");
    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($_GET["PurchaseOrderNumber"]);
    if(!$purchaseOrderNumber) sendResponse(NULL, "Purchase Order Number Parser Error");

    $data = json_decode(file_get_contents('php://input'),true);

    $dbLink = dbConnect();

    $error = null;

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
    $query = dbBuildUpdateQuery($dbLink, "purchaseOrder", $poData, "PoNo = ".$purchaseOrderNumber);

    $result = dbRunQuery($dbLink,$query);

    $output = array();

    dbClose($dbLink);
    sendResponse($output,$error);
}
?>