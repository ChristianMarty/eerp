<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/finance/costCenter/
// Author   : Christian Marty
// Date		: 15.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

    if(!isset($_GET["CostCenterNumber"])) sendResponse(NULL, "Cost Center Number Undefined");

    $costCenterNumber = barcodeParser_CostCenter($_GET["CostCenterNumber"]);

    $query = <<<STR
        SELECT * FROM finance_costCenter
        WHERE CostCenterNumber = '$costCenterNumber'
    STR;

	$result = dbRunQuery($dbLink,$query);

	$costCenterData = mysqli_fetch_assoc($result);
    $costCenterData['Barcode'] = barcodeFormatter_CostCenter($costCenterData['CostCenterNumber']);
    $costCenterData['Id'] = intval($costCenterData['Id']);

    $costCenterDataId =  $costCenterData['Id'];

    $query = <<<STR
        SELECT 
        purchaseOrder.PurchaseDate, supplier.Name AS supplier, purchaseOrder.PoNo AS PoNo,
        purchaseOrder_itemOrder.LineNo, purchaseOrder_itemOrder.OrderReference, purchaseOrder_itemOrder.PartNo,
        purchaseOrder_itemOrder.ManufacturerName, -- manufacturer.Name AS Manufacturer,
        purchaseOrder_itemOrder.ManufacturerPartNumber, purchaseOrder_itemOrder.Description,
        purchaseOrder_itemOrder.Note,purchaseOrder_itemOrder.ExpectedReceiptDate, purchaseOrder_itemOrder.Quantity,
        purchaseOrder_itemOrder.Price,finance_currency.CurrencyCode AS Currency,
        purchaseOrder.ExchangeRate, purchaseOrder.PaymentTerms, purchaseOrder.Title
        FROM purchaseOrder_itemOrder
        LEFT JOIN purchaseOrder_itemOrder_costCenter_mapping ON purchaseOrder_itemOrder_costCenter_mapping.ItemOrderId = purchaseOrder_itemOrder.Id 
        LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId 
        LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId 
        LEFT JOIN vendor AS supplier ON supplier.Id = purchaseOrder.VendorId
        LEFT JOIN supplierPart ON supplierPart.Id = purchaseOrder_itemOrder.SupplierPartId 
        -- LEFT JOIN manufacturerPart ON manufacturerPart.Id = supplierPart.ManufacturerPartId
        -- LEFT JOIN vendor AS manufacturer ON manufacturer.Id = manufacturerPart.VendorId
        WHERE purchaseOrder_itemOrder_costCenter_mapping.CostCenterId = $costCenterDataId;
    STR;

    $output = array();
    $result = dbRunQuery($dbLink,$query);
    while($r = mysqli_fetch_assoc($result))
    {
        $r['PurchaseOrderBarcode']  = barcodeFormatter_PurchaseOrderNumber($r['PoNo'], $r['LineNo']);
        $output[] = $r;
    }

    $costCenterData['PurchaseItem'] = $output;

	dbClose($dbLink);	
	sendResponse($costCenterData);
}
?>