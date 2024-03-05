<?php

namespace stock;
require_once __DIR__ . "/../util/_barcodeParser.php";

class stock
{
    static function createOnReceival(
        int $receivalId,
        int $locationNumber,
        int $quantity,
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
        }
        catch (\Exception $e)
        {
            $database->rollBackTransaction();
            throw new \Exception($e->getMessage());
        }

        $insertData = [];
        $insertData['StockId'] = $stockId;
        $insertData['Quantity'] = $quantity;
        $insertData['ChangeType'] ="Create";
        $insertData['CreationUserId'] = $user->userId();

        try {
            $database->insert("partStock_history", $insertData);
        }
        catch (\Exception $e)
        {
            $database->rollBackTransaction();
            throw new \Exception($e->getMessage());
        }

        $database->commitTransaction();

        return $database->query("SELECT StockNumber FROM partStock WHERE Id = $stockId")[0]->StockNumber;
    }

    static function create(
        int $manufacturerId,
        string $manufacturerPartNumber,
        string $locationNumber,
        int $quantity,
        string|null $date,
        string|null $lotNumber,
        string|null $orderReference,
        int|null $supplierId,
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

        if(count($manufacturerPartNumberData) == 0)
        {
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
        }else{
            $manufacturerPartNumberId = $manufacturerPartNumberData[0]->ManufacturerPartNumberId;
        }

        $supplierPartId = null;
        if($supplierId !== null)
        {
            $supplierPartNumber = $database->escape($supplierPartNumber);
            $query = <<< QUERY
                SELECT Id FROM supplierPart WHERE supplierPart.VendorId = $supplierId AND supplierPart.SupplierPartNumber = $supplierPartNumber
            QUERY;

            $supplierPartData = $database->query($query);

            if(count($supplierPartData) == 0) {
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
            }else{
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
        }
        catch (\Exception $e)
        {
            $database->rollBackTransaction();
            throw new \Exception($e->getMessage());
        }

        $insertData = [];
        $insertData['StockId'] = $stockId;
        $insertData['Quantity'] = $quantity;
        $insertData['ChangeType'] ="Create";
        $insertData['CreationUserId'] = $user->userId();

        try {
            $database->insert("partStock_history", $insertData);
        }
        catch (\Exception $e)
        {
            $database->rollBackTransaction();
            throw new \Exception($e->getMessage());
        }

        $database->commitTransaction();

        return $database->query("SELECT StockNumber FROM partStock WHERE ID  = $stockId")[0]->StockNumber;
    }

}