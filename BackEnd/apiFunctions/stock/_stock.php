<?php

namespace stock;
require_once __DIR__ . "/../util/_barcodeParser.php";

class stock
{
    static function createOnReceival(
        int         $receivalId,
        int         $locationNumber,
        int         $quantity,
        string|null $date,
        string|null $lotNumber,
        string|null $orderReference
    ): string
    {
        global $database;
        global $user;

        $queryManufacturerPartNumberId = <<< QUERY
            SELECT supplierPart.ManufacturerPartNumberId  FROM  purchaseOrder_itemReceive 
            LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemOrder.Id = purchaseOrder_itemReceive.ItemOrderId
            LEFT JOIN supplierPart ON supplierPart.Id = purchaseOrder_itemOrder.SupplierPartId
            WHERE purchaseOrder_itemReceive.Id = $receivalId
        QUERY;

        $insertData = [];
        $insertData['StockNumber']['raw'] = "partStock_generateStockNumber()";
        $insertData['ManufacturerPartNumberId']['raw'] = "($queryManufacturerPartNumberId)";
        $insertData['LocationId']['raw'] = "(SELECT `Id` FROM `location` WHERE `LocationNumber`= $locationNumber)";
        $insertData['Date'] = $date;
        $insertData['OrderReference'] = $orderReference;
        $insertData['ReceivalId'] = $receivalId;
        $insertData['LotNumber'] = $lotNumber;
        $insertData['CreationUserId'] = $user->userId();

        $database->beginTransaction();

        try {
            $database->insert("partStock", $insertData);
            $stockId = $database->lastInsertId();
        } catch (\Exception $e) {
            $database->rollBackTransaction();
            throw new \Exception($e->getMessage());
        }

        $insertData = [];
        $insertData['StockId'] = $stockId;
        $insertData['Quantity'] = $quantity;
        $insertData['ChangeType'] = "Create";
        $insertData['CreationUserId'] = $user->userId();

        try {
            $database->insert("partStock_history", $insertData);
        } catch (\Exception $e) {
            $database->rollBackTransaction();
            throw new \Exception($e->getMessage());
        }

        $database->commitTransaction();

        return $database->query("SELECT StockNumber FROM partStock WHERE Id = $stockId")[0]->StockNumber;
    }

    static function create(
        int         $manufacturerId,
        string      $manufacturerPartNumber,
        string      $locationNumber,
        int         $quantity,
        string|null $date,
        string|null $lotNumber,
        string|null $orderReference,
        int|null    $supplierId,
        string|null $supplierPartNumber
    ): string
    {
        global $database;
        global $user;

        $database->beginTransaction();

        $manufacturerPartNumberIdQuery = <<< QUERY
            SELECT manufacturerPart_partNumber.Id AS ManufacturerPartNumberId
            FROM manufacturerPart_partNumber
            LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
            LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
            WHERE (manufacturerPart_partNumber.VendorId <=> $manufacturerId OR manufacturerPart_item.VendorId <=> $manufacturerId OR manufacturerPart_series.VendorId <=> $manufacturerId)
            AND manufacturerPart_partNumber.Number = $manufacturerPartNumber
        QUERY;

        $manufacturerPartNumberData = $database->query($manufacturerPartNumberIdQuery);

        if (count($manufacturerPartNumberData) == 0) {
            $insertData = [];
            $insertData['VendorId'] = $manufacturerId;
            $insertData['Number'] = $manufacturerPartNumber;
            $insertData['CreationUserId'] = $user->userId();

            try {
                $database->insert("manufacturerPart_partNumber", $insertData);
                $manufacturerPartNumberId = $database->lastInsertId();
            } catch (\Exception $e) {
                $database->rollBackTransaction();
                throw new \Exception($e->getMessage());
            }
        } else {
            $manufacturerPartNumberId = $manufacturerPartNumberData[0]->ManufacturerPartNumberId;
        }

        $supplierPartId = null;
        if ($supplierId !== null) {
            $supplierPartNumber = $database->escape($supplierPartNumber);
            $query = <<< QUERY
                SELECT Id FROM supplierPart WHERE supplierPart.VendorId = $supplierId AND supplierPart.SupplierPartNumber = $supplierPartNumber
            QUERY;

            $supplierPartData = $database->query($query);

            if (count($supplierPartData) == 0) {
                $insertData = [];
                $insertData['ManufacturerPartNumberId'] = $manufacturerPartNumberId;
                $insertData['VendorId'] = $supplierId;
                $insertData['SupplierPartNumber'] = $supplierPartNumber;
                $insertData['CreationUserId'] = $user->userId();

                try {
                    $database->insert("supplierPart", $insertData);
                    $supplierPartId = $database->lastInsertId();
                } catch (\Exception $e) {
                    $database->rollBackTransaction();
                    throw new \Exception($e->getMessage());
                }
            } else {
                $supplierPartId = $supplierPartData[0]->Id;
            }
        }

        $insertData = [];
        $insertData['StockNumber']['raw'] = "partStock_generateStockNumber()";
        $insertData['ManufacturerPartNumberId'] = $manufacturerPartNumberId;
        $insertData['LocationId']['raw'] = "(SELECT `Id` FROM `location` WHERE `LocationNumber`= $locationNumber)";
        $insertData['Date'] = $date;
        $insertData['OrderReference'] = $orderReference;
        $insertData['ReceivalId'] = null;
        $insertData['SupplierPartId'] = $supplierPartId;
        $insertData['LotNumber'] = $lotNumber;
        $insertData['CreationUserId'] = $user->userId();

        $database->beginTransaction();

        try {
            $database->insert("partStock", $insertData);
            $stockId = $database->lastInsertId();
        } catch (\Exception $e) {
            $database->rollBackTransaction();
            throw new \Exception($e->getMessage());
        }

        $insertData = [];
        $insertData['StockId'] = $stockId;
        $insertData['Quantity'] = $quantity;
        $insertData['ChangeType'] = "Create";
        $insertData['CreationUserId'] = $user->userId();

        try {
            $database->insert("partStock_history", $insertData);
        } catch (\Exception $e) {
            $database->rollBackTransaction();
            throw new \Exception($e->getMessage());
        }

        $database->commitTransaction();

        return $database->query("SELECT StockNumber FROM partStock WHERE ID  = $stockId")[0]->StockNumber;
    }

    static function certainty(int $stockId): \stdClass
    {
        global $database;

        $query = <<<STR
            SELECT * FROM partStock_history_sinceLastCount
            WHERE StockId = $stockId
        STR;

        $result = $database->query($query);

        $daysSinceStocktaking = NULL;
        $lastStocktakingDate = NULL;
        $certaintyFactor = 1;

        $movements = array();
        foreach ($result as $item) {
            if ($item->ChangeType == 'Absolute' || $item->ChangeType == 'Create') {
                $earlier = new \DateTime($item->CreationDate);
                $later = new \DateTime();

                $daysSinceStocktaking = $later->diff($earlier)->format("%a");
                $lastStocktakingDate = $item->CreationDate;
            } else {
                $movements[] = $item;
            }
        }

        if ($daysSinceStocktaking > 1) // If not counted today
        {
            // TODO: Make this better
            $noOfMoves = count($movements);
            $certaintyFactor -= ($noOfMoves * 0.025);

            $certaintyFactor -= ($daysSinceStocktaking * 0.0025);

            if ($certaintyFactor < 0) $certaintyFactor = 0;
        }

        $output = new \stdClass();
        $output->Factor = round($certaintyFactor, 4);
        $output->Rating = round($output->Factor * 5);
        $output->DaysSinceStocktaking = intval($daysSinceStocktaking);
        $output->LastStocktakingDate = $lastStocktakingDate;

        return $output;
    }

    static function purchaseInformation(int $stockId): \stdClass|null
    {
        global $database;

        $query = <<<STR
            SELECT 
                PurchaseOrderNumber, 
                LineNumber,
                Price, 
                Discount,
                finance_currency.CurrencyCode AS CurrencyCode, 
                PurchaseDate,
                OrderReference,
                Quantity,
                purchaseOrder_itemOrder.Description AS Description,
                VendorId AS SupplierId,
                vendor_displayName(VendorId) AS SupplierName
            FROM purchaseOrder_itemOrder
            LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id 
            LEFT JOIN purchaseOrder ON purchaseOrder.Id = purchaseOrder_itemOrder.PurchaseOrderId 
            LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId 
            WHERE purchaseOrder_itemReceive.Id = (SELECT partStock.ReceivalId FROM partStock WHERE Id = '$stockId')
        STR;

        $output = $database->query($query);

        if (count($output) === 0)  return null;
        $output = $output[0];

        $output->PurchaseOrderNumber = intval($output->PurchaseOrderNumber);
        $output->ItemCode = barcodeFormatter_PurchaseOrderNumber($output->PurchaseOrderNumber, $output->LineNumber);

        $output->LineNumber = intval($output->LineNumber);
        $output->Price = floatval($output->Price);
        $output->Discount = floatval($output->Discount);
        $output->PriceAfterDiscount = $output->Price*(($output->Discount/100)+1);
        $output->SupplierName = $output->SupplierName ?? "";
        $output->SupplierId = intval($output->SupplierId);
        $output->Quantity = floatval($output->Quantity);
        $output->OrderReference = $output->OrderReference ?? "";
        $output->PurchaseDate = $output->PurchaseDate ?? "";
        $output->Description = $output->Description ?? "";

        return $output;
    }
}