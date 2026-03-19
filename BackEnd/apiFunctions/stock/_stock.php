<?php

namespace Stock;

class Stock
{
    static function createOnReceival(
        int         $receivalId,
        int         $locationNumber,
        int         $quantity,
        string|null $date,
        string|null $lotNumber
    ): string | \Error\Data
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
        $insertData['ManufacturerPartNumberId']['raw'] = "($queryManufacturerPartNumberId)";
        $insertData['LocationId']['raw'] = "(SELECT `Id` FROM `location` WHERE `LocationNumber`= $locationNumber)";
        $insertData['Date'] = $date;
        $insertData['ReceivalId'] = $receivalId;
        $insertData['LotNumber'] = $lotNumber;

        $database->beginTransaction();

        $stockId = self::createPartStockEntry($insertData, $quantity);
        if(\Error\checkError($stockId)){
            $database->rollBackTransaction();
            return $stockId;
        }

        $database->commitTransaction();

        return $database->query("SELECT StockNumber FROM partStock WHERE Id = $stockId")[0]->StockNumber;
    }

    static function create(
        int         $manufacturerId,
        string      $manufacturerPartNumber,
        int|null    $locationNumber,
        int         $quantity,
        string|null $date,
        string|null $lotNumber,
        int|null    $supplierId,
        string|null $supplierPartNumber
    ): string | \Error\Data
    {
        global $database;
        global $user;

        $manufacturerPartNumber = trim($manufacturerPartNumber);
        $manufacturerPartNumberEscaped = $database->escape($manufacturerPartNumber);

        $date = $date === null ? null : trim($date);
        $supplierPartNumber = $supplierPartNumber === null ? null : trim($supplierPartNumber);

        $database->beginTransaction();

        $manufacturerPartNumberIdQuery = <<< QUERY
            SELECT manufacturerPart_partNumber.Id AS ManufacturerPartNumberId
            FROM manufacturerPart_partNumber
            LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
            LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
            WHERE (manufacturerPart_partNumber.VendorId <=> $manufacturerId OR manufacturerPart_item.VendorId <=> $manufacturerId OR manufacturerPart_series.VendorId <=> $manufacturerId)
            AND manufacturerPart_partNumber.Number = $manufacturerPartNumberEscaped
        QUERY;

        $manufacturerPartNumberData = $database->query($manufacturerPartNumberIdQuery);

        if (count($manufacturerPartNumberData) == 0) {
            $insertData = [];
            $insertData['VendorId'] = $manufacturerId;
            $insertData['Number'] = $manufacturerPartNumber;
            $insertData['CreationUserId'] = $user->userId();

            $result = $database->insert("manufacturerPart_partNumber", $insertData);
            if(\Error\checkError($result)){
                $database->rollBackTransaction();
                return $result;
            }
            $manufacturerPartNumberId = $database->lastInsertId();

        } else {
            $manufacturerPartNumberId = $manufacturerPartNumberData[0]->ManufacturerPartNumberId;
        }

        $supplierPartId = null;
        if ($supplierId !== null && $supplierId !== 0) {
            $supplierPartNumberEscaped = $database->escape($supplierPartNumber);
            $query = <<< QUERY
                SELECT Id FROM supplierPart WHERE supplierPart.VendorId = $supplierId AND supplierPart.SupplierPartNumber = $supplierPartNumberEscaped
            QUERY;

            $supplierPartData = $database->query($query);

            if (count($supplierPartData) == 0) {
                $insertData = [];
                $insertData['ManufacturerPartNumberId'] = $manufacturerPartNumberId;
                $insertData['VendorId'] = $supplierId;
                $insertData['SupplierPartNumber'] = $supplierPartNumber;
                $insertData['CreationUserId'] = $user->userId();

                $result = $database->insert("supplierPart", $insertData);
                if(\Error\checkError($result)){
                    $database->rollBackTransaction();
                    return $result;
                }
                $supplierPartId = $database->lastInsertId();

            } else {
                $supplierPartId = $supplierPartData[0]->Id;
            }
        }

        $insertData = [];
        $insertData['ManufacturerPartNumberId'] = $manufacturerPartNumberId;
        $insertData['LocationId']['raw'] = "(SELECT `Id` FROM `location` WHERE `LocationNumber`= $locationNumber)";
        $insertData['Date'] = $date;
        $insertData['ReceivalId'] = null;
        $insertData['SupplierPartId'] = $supplierPartId;
        $insertData['LotNumber'] = $lotNumber;

        $stockId = self::createPartStockEntry($insertData, $quantity);
        if(\Error\checkError($stockId)){
            $database->rollBackTransaction();
            return $stockId;
        }

        $database->commitTransaction();

        return $database->query("SELECT StockNumber FROM partStock WHERE ID  = $stockId")[0]->StockNumber;
    }

    static private function createPartStockEntry(array $data, float $quantity): int | \Error\Data
    {
        global $database;
        global $user;

        $stockNumber = self::newStockCode();
        if(\Error\checkError($stockNumber)){
            return $stockNumber;
        }

        $data['StockNumber'] = $stockNumber;
        $data['CreationUserId'] = $user->userId();

        $result = $database->insert("partStock", $data);
        if(\Error\checkError($result)){
            return $result;
        }
        $stockId = $database->lastInsertId();

        $insertData = [];
        $insertData['StockId'] = $stockId;
        $insertData['Quantity'] = $quantity;
        $insertData['ChangeType'] = "Create";
        $insertData['CreationUserId'] = $user->userId();

        $result = $database->insert("partStock_history", $insertData);
        if(\Error\checkError($result)){
            return $result;
        }

        return $stockId;
    }

    static function split(string $stockCode, float $quantity): string | \Error\Data
    {
        global $database;

        $stockNumberQuoted = $database->escape($stockCode);
        $query = <<<STR
		SELECT 
		    partStock_history.Id,
			partStock_history.Quantity
		FROM partStock_history 
		LEFT JOIN partStock ON partStock_history.StockId = partStock.Id
		WHERE partStock.StockNumber =  $stockNumberQuoted AND (ChangeType = 'Absolute' OR ChangeType = 'Create')
	STR;
        $historyResult = $database->query($query);

        if(\Error\checkError($historyResult)){
            return $historyResult;
        }

        if(\Error\checkNoResult($historyResult)){
            return \Error\itemNotFound($stockCode);
        }

        foreach($historyResult as $item){
            if($item->Quantity < $quantity){
                return \Error\generic("Split quantity bigger as stock quantity");
            }
        }


        $database->beginTransaction();

        $query = <<<STR
            SELECT 
                ManufacturerPartNumberId,
                SpecificationPartRevisionId,
                AssemblyId,
                Date,
                CountryOfOriginCountryId,
                LocationId,
                HomeLocationId,
                SupplierPartId,
                ReceivalId,
                LotNumber
            FROM partStock 
            WHERE partStock.StockNumber =  $stockNumberQuoted 
        STR;
        $result = $database->query($query);
        if(\Error\checkError($result)){
            return $result;
        }
        if(\Error\checkNoResult($result)){
            return \Error\itemNotFound($stockCode);
        }

        $oldItem = $result[0];

        $insertData = [];
        $insertData['ManufacturerPartNumberId'] = $oldItem->ManufacturerPartNumberId;
        $insertData['SpecificationPartRevisionId'] = $oldItem->SpecificationPartRevisionId;
        $insertData['AssemblyId'] = $oldItem->AssemblyId;
        $insertData['Date'] = $oldItem->Date;
        $insertData['CountryOfOriginCountryId'] = $oldItem->CountryOfOriginCountryId;
        $insertData['LocationId'] = $oldItem->LocationId;
        $insertData['HomeLocationId'] = $oldItem->HomeLocationId;
        $insertData['SupplierPartId'] = $oldItem->SupplierPartId;
        $insertData['ReceivalId'] = $oldItem->ReceivalId;
        $insertData['LotNumber'] = $oldItem->LotNumber;

        $newStockId = self::createPartStockEntry($insertData, $quantity);
        if(\Error\checkError($newStockId)){
            $database->rollBackTransaction();
            return $newStockId;
        }

        foreach($historyResult as $item){
            $id = $item->Id;
            $updateData = [];
            $updateData['Quantity'] = $item->Quantity - $quantity;
            $updateResult = $database->update('partStock_history', $updateData, "Id = $id");
            if(\Error\checkError($updateResult)) {
                $database->rollBackTransaction();
                return $updateResult;
            }
        }

        $query = <<<STR
            SELECT 
                StockNumber
            FROM partStock 
            WHERE partStock.Id = $newStockId
        STR;
        $itemResult = $database->query($query);
        if(\Error\checkError($itemResult)) {
            $database->rollBackTransaction();
            return $itemResult;
        }

        if(count($itemResult) !== 1) {
            $database->rollBackTransaction();
            return \Error\generic("Stock split failed");
        }

        $database->commitTransaction();

        return $itemResult[0]->StockNumber;
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
                PartNo AS ProductionPartNumber,
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
        $output->ItemCode = \Numbering\format(\Numbering\Category::PurchaseOrder, $output->PurchaseOrderNumber, $output->LineNumber);

        $output->LineNumber = intval($output->LineNumber);
        $output->Price = floatval($output->Price);
        $output->Discount = floatval($output->Discount);
        $output->PriceAfterDiscount = $output->Price*(($output->Discount/100)+1);
        $output->SupplierName = $output->SupplierName ?? "";
        $output->SupplierId = intval($output->SupplierId);
        $output->Quantity = floatval($output->Quantity);
        $output->OrderReference = $output->OrderReference ?? "";
        $output->ProductionPartNumber = $output->ProductionPartNumber ?? "";
        $output->PurchaseDate = $output->PurchaseDate ?? "";
        $output->Description = $output->Description ?? "";

        return $output;
    }

    static function createCountingRequest(
        int|null    $stockId,
        string|null $stockNumber = null
    ): null | \Error\Data
    {
        if($stockId === null and $stockNumber === null){
            return \Error\generic("No input");
        }

        global $database;
        global $user;

        $sqlData['CountingRequestUserId'] = $user->userId();
        $sqlData['CountingRequestDate']['raw'] = "current_timestamp()";

        if($stockId !== null){
            return $database->update("partStock", $sqlData, "Id = $stockId");
        }else if($stockNumber !== null){
            $stockNumber = $database->escape($stockNumber);
            return $database->update("partStock", $sqlData, "StockNumber = $stockNumber");
        }
        return null;
    }

    static function clearCountingRequest(
        int|null    $stockId,
        string|null $stockNumber = null
    ): void
    {
        if($stockId === null and $stockNumber === null){
            return;
        }

        global $database;

        $sqlData['CountingRequestUserId'] = null;
        $sqlData['CountingRequestDate'] = null;

        if($stockId !== null){
            $database->update("partStock", $sqlData, "Id = $stockId");
        }else if($stockNumber !== null){
            $stockNumber = $database->escape($stockNumber);
            $database->update("partStock", $sqlData, "StockNumber = $stockNumber");
        }
    }

    static private function newStockCode(): string | \Error\Data
    {
        global $database;

        for ($i = 0; $i < 10; $i++) {
            $string = self::newRandomStockCodeString();

            $query = <<< QUERY
                SELECT COUNT(Id) AS 'Exists' FROM partStock WHERE StockNumber = '$string';
            QUERY;

            $result = $database->query($query);
            if (\Error\checkError($result)) {
                return $result;
            }

            if ($result[0]->Exists === 0) {
                return $string;
            }
        }

        return \Error\generic("Unable to generate a new stock code");
    }

    static private function newRandomStockCodeString(): string
    {
        $characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ0123456789';
        $randomString = '';
        for ($i = 0; $i < 4; $i++) {
            $randomString .= $characters[rand(0,strlen($characters)-1)];
        }
        return $randomString;
    }

/*
    function formatHistoryItem(\stdClass $itemData): \stdClass|null
    {
        $description = "";
        $type = null;

        if($itemData->ChangeType == 'Relative')
        {
            if($itemData->Quantity >0 )
            {
                $description = "Add ".$itemData->Quantity."pcs";
                $type = "add";
                $quantity += intval($item->Quantity,10);
            }
            else
            {
                $description = "Remove ".abs($itemData->Quantity)."pcs";
                $type = "remove";
                $quantity += intval($item->Quantity,10);
            }
        }
        else if($itemData->ChangeType == 'Absolute')
        {
            $description = "Stocktaking";
            $type = "count";
            $quantity = intval($itemData->Quantity,10);
        }
        else if($itemData->ChangeType == 'Create')
        {
            $description = "Create";
            $type = "create";
            $quantity = intval($item->Quantity,10);
        }

        $description .= ", New Quantity: ".$quantity;

        $output = new \stdClass();
        $output->ItemCode = barcodeFormatter_StockHistoryNumber($stockNumber, $itemData->ChangeIndex);
        $output->Type = $type;
        $output->Date = $item->Date;
        $output->Note = $item->Note;
        $output->Description = trim($description);
        $output->WorkOrderCode = barcodeFormatter_WorkOrderNumber($item->WorkOrderNumber);
        $output->NameInitials = $item->Initials;

        return $output;
    }*/
}