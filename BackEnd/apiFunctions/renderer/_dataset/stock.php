<?php
//*************************************************************************************************
// FileName : stock.php
// FilePath : apiFunctions/dataset/_dataset
// Author   : Christian Marty
// Date		: 16.11.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace renderer;
require_once "_dataset.php";

class StockData {
    public string $itemCode;
    public string $stockNumber;
    public int $changeIndex;
    public array  $productionPartNumber;
    public string $typeDescription;
    public float  $quantity;
    public string $manufacturerName;
    public string $manufacturerPartNumber;
    public string|null $workOrderNumber;
    public string|null $workOrderName;
    public string|null $partDescription;
    public string|null $note;

    public float|null $singlePartWeight;
    public string|null $singlePartWeightSymbol;
    public string|null $countryOfOriginName;
    public string|null $countryOfOriginCode;
}

class Stock extends \renderer\dataset
{
    /**
     * @param array<string>|string $stockHistoryItem
     * @return array<StockData>|null
     */
    static function getData(string|array $stockHistoryItem) : array|null
    {
        global $database;

        $items = [];
        if(is_array($stockHistoryItem)) {
            foreach ($stockHistoryItem as $item) {
                $stockNumber = \Numbering\parser(\Numbering\Category::Stock, $item);
                $historyItem = \Numbering\parser(\Numbering\Category::StockHistoryIndex, $item);
                if($stockNumber !== null && $historyItem !== null) {
                    $items[] = $stockNumber . "-" . $historyItem;
                }
            }
        }else{
            $items[] = $stockHistoryItem;
        }

        if(empty($items)){
            return null;
        }

        $itemListString = "'".implode("','",$items)."'";
        $query = <<< STR
            SELECT 
                partStock.StockNumber, 
                vendor_displayName(manufacturer.Id) AS ManufacturerName, 
                manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
                manufacturerPart_partNumber.Description AS PartNumberDescription,
                manufacturerPart_item.Description AS PartItemDescription,
                partStock_history.Cache_ChangeIndex AS ChangeIndex,
                partStock_history.ChangeType,
                partStock_history.Quantity,
                partStock_history.Note,
                workOrder.WorkOrderNumber,
                workOrder.Name AS WorkOrderName,
                GROUP_CONCAT(CONCAT(numbering.Prefix,'-',productionPart.Number) ) AS ProductionPartNumberList,
                productionPart.Description AS ProductionPartDescription,
            
                manufacturerPart_partNumber.SinglePartWeight AS SinglePartWeight,
                country.ShortName as CountryOfOriginName,
                country.Alpha2Code as CountryOfOriginCode
            
            FROM partStock_history
            LEFT JOIN partStock ON partStock_history.StockId = partStock.Id
            LEFT JOIN workOrder ON workOrder.Id = partStock_history.WorkOrderId
            LEFT JOIN (
                SELECT SupplierPartId, purchaseOrder_itemReceive.Id FROM purchaseOrder_itemOrder
                LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemOrder.Id = purchaseOrder_itemReceive.ItemOrderId
                )poLine ON poLine.Id = partStock.ReceivalId
            LEFT JOIN supplierPart ON (supplierPart.Id = partStock.SupplierPartId AND partStock.ReceivalId IS NULL) OR (supplierPart.Id = poLine.SupplierPartId)   
            LEFT JOIN manufacturerPart_partNumber ON (manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId AND supplierPart.ManufacturerPartNumberId IS NULL) OR manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId
            LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
            LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
            LEFT JOIN (SELECT Id, vendor_displayName(id) FROM vendor)manufacturer ON manufacturer.Id <=> manufacturerPart_item.VendorId OR manufacturer.Id <=> manufacturerPart_partNumber.VendorId OR manufacturer.Id <=> manufacturerPart_series.VendorId
            LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
            LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
            LEFT JOIN numbering ON productionPart.NumberingPrefixId = numbering.Id
            LEFT JOIN country On country.Id = partStock.CountryOfOriginCountryId
            WHERE CONCAT(partStock.StockNumber,"-",partStock_history.Cache_ChangeIndex ) IN ($itemListString)
            GROUP BY partStock_history.Id
        STR;
        $stockHistoryItems = $database->query($query);

        if(empty($stockHistoryItems)){
            return null;
        }

        $return = [];
        foreach($stockHistoryItems as $item)
        {
            $stockData = new StockData();

            if($item->ChangeType == "Create"){
                $stockData->typeDescription = "Create";
            }else if($item->ChangeType == 'Absolute') {
                $stockData->typeDescription = "Stocktaking";
            }else if($item->ChangeType == 'Relative') {
                if($item->Quantity > 0 ){
                    $stockData->typeDescription = "Add";
                }else {
                    $stockData->typeDescription = "Remove";
                }
            }
            $stockData->quantity = abs($item->Quantity);

            $stockData->itemCode = \Numbering\format(\Numbering\Category::Stock, $item->StockNumber, $item->ChangeIndex);
            $stockData->stockNumber = $item->StockNumber;
            $stockData->changeIndex = $item->ChangeIndex;

            if($item->ProductionPartNumberList !== null) {
                $stockData->productionPartNumber = explode(",", $item->ProductionPartNumberList);
            }else{
                $stockData->productionPartNumber = [];
            }

            $stockData->manufacturerName = $item->ManufacturerName;
            $stockData->manufacturerPartNumber = $item->ManufacturerPartNumber;

            $stockData->workOrderNumber = \Numbering\format(\Numbering\Category::WorkOrder, $item->WorkOrderNumber);
            $stockData->workOrderName = $item->WorkOrderName;

            if($item->PartNumberDescription){
                $stockData->partDescription = $item->PartNumberDescription;
            }elseif($item->PartItemDescription){
                $stockData->partDescription = $item->PartItemDescription;
            }elseif($item->ProductionPartDescription){
                $stockData->partDescription = $item->ProductionPartDescription;
            }else{
                $stockData->partDescription = null;
            }

            $stockData->note = $item->Note;

            $stockData->singlePartWeight = $item->SinglePartWeight;
            $stockData->singlePartWeightSymbol = "g";
            $stockData->countryOfOriginName = $item->CountryOfOriginName;
            $stockData->countryOfOriginCode = $item->CountryOfOriginCode;

            $return[] = $stockData;
        }

        return $return;
    }

}