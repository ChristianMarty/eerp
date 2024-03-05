<?php
//*************************************************************************************************
// FileName : getPurchaseInformation.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 17.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

function getPurchaseInformation($receivalId): array
{
    global $database;

    $query = <<<STR
        SELECT 
            PurchaseOrderNumber, 
            Price, 
            finance_currency.CurrencyCode AS Currency, 
            PurchaseDate, 
            Sku AS SupplierPartNumber, 
            vendor_displayName(vendor.Id) AS SupplierName, 
            OrderReference, 
            vendor.Id AS VendorId 
        FROM purchaseOrder_itemOrder
        LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id
        LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId
        LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId
        LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId
        WHERE purchaseOrder_itemReceive.Id = $receivalId
    STR;
    return $database->query($query);
}
