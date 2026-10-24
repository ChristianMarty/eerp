<?php
//*************************************************************************************************
// FileName : _purchasing.php
// FilePath : apiFunctions/purchasing
// Author   : Christian Marty
// Date		: 24.10.2026
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace Purchasing;

require_once __DIR__ . "/../../core/numbering.php";

class PurchaseOrderData implements \JsonSerializable
{
    public string $itemCode;
    public string $title;
    public string $date;

    public string $supplierName;
    public int $supplierId;
    public string $sku;
    public float $quantity;
    public float $price;

    public string $status;


    public function jsonSerialize(): \stdClass
    {
        $output = new \stdClass();
        $output->ItemCode = $this->itemCode;
        $output->Title = $this->title;
        $output->Date = $this->date;

        $output->SupplierName = $this->supplierName;
        $output->SupplierId = $this->supplierId;
        $output->Sku = $this->sku;
        $output->Quantity = $this->quantity;
        $output->Price = $this->price;
        $output->Status = $this->status;

        return $output;
    }
}

function getPurchaseOrderFromPartNumberId(int $ManufacturerPartNumberId): \Error\Data | array // of PurchaseOrderData
{
    global $database;

    $query = <<<STR
        SELECT
            purchaseOrder.PurchaseOrderNumber AS PurchaseOrderNumber,
            purchaseOrder_itemOrder.LineNumber AS PurchaseOrderLine,
            purchaseOrder.Title AS PurchaseOrderTitle,
            purchaseOrder.PurchaseDate AS PurchaseOrderDate,
            SUM(QuantityReceived) AS TotalQuantityReceived,
            vendor_displayName(vendor.Id) AS SupplierName,
            vendor.Id AS SupplierId,
            purchaseOrder_itemOrder.Sku,
            purchaseOrder_itemOrder.Price,
            purchaseOrder.Status
        FROM purchaseOrder_itemOrder
        LEFT JOIN supplierPart ON supplierPart.Id = purchaseOrder_itemOrder.SupplierPartId
        LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId
        LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = supplierPart.ManufacturerPartNumberId
        LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id
        LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE supplierPart.ManufacturerPartNumberId = $ManufacturerPartNumberId
        GROUP BY purchaseOrder_itemOrder.Id
    STR;

    $result = $database->query($query);
    if(\Error\checkError($result)){
        return \Error\database((string)$result);
    }

    $output = [];
    foreach ($result as $r)
    {
        $item = new PurchaseOrderData();
        $item->itemCode = \Numbering\format(\Numbering\Category::PurchaseOrder, $r->PurchaseOrderNumber, $r->PurchaseOrderLine);
        $item->title = $r->PurchaseOrderTitle;
        $item->date = $r->PurchaseOrderDate;
        $item->supplierName = $r->SupplierName;
        $item->supplierId = intval($r->SupplierId);
        $item->sku = $r->Sku;
        $item->quantity = floatval($r->TotalQuantityReceived);
        $item->price  = floatval($r->Price);
        $item->status = $r->Status;

        $output[] = $item;
    }
    return $output;
}
