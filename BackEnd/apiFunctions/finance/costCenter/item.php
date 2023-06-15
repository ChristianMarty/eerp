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
        purchasOrder.PurchaseDate, supplier.Name AS supplier, purchasOrder.PoNo AS PoNo,
        purchasOrder_itemOrder.LineNo, purchasOrder_itemOrder.OrderReference, purchasOrder_itemOrder.PartNo,
        purchasOrder_itemOrder.ManufacturerName, -- manufacturer.Name AS Manufacturer,
        purchasOrder_itemOrder.ManufacturerPartNumber, purchasOrder_itemOrder.Description,
        purchasOrder_itemOrder.Note,purchasOrder_itemOrder.ExpectedReceiptDate, purchasOrder_itemOrder.Quantity,
        purchasOrder_itemOrder.Price,finance_currency.CurrencyCode AS Currency,
        purchasOrder.ExchangeRate, purchasOrder.PaymentTerms, purchasOrder.Title
        FROM purchasOrder_itemOrder
        LEFT JOIN purchasOrder_itemOrder_costCenter_mapping ON purchasOrder_itemOrder_costCenter_mapping.ItemOrderId = purchasOrder_itemOrder.Id 
        LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId 
        LEFT JOIN finance_currency ON finance_currency.Id = purchasOrder.CurrencyId 
        LEFT JOIN vendor AS supplier ON supplier.Id = purchasOrder.VendorId
        LEFT JOIN supplierPart ON supplierPart.Id = purchasOrder_itemOrder.SupplierPartId 
        -- LEFT JOIN manufacturerPart ON manufacturerPart.Id = supplierPart.ManufacturerPartId
        -- LEFT JOIN vendor AS manufacturer ON manufacturer.Id = manufacturerPart.VendorId
        WHERE purchasOrder_itemOrder_costCenter_mapping.CostCenterId = $costCenterDataId;
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