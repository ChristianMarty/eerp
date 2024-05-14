<?php
//*************************************************************************************************
// FileName : partHistoryLabel.php
// FilePath : apiFunctions/print/
// Author   : Christian Marty
// Date		: 10.05.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once "_renderer.php";

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

class partHistoryLabel extends \renderer\renderer
{
    protected ?\renderer\language $method = \renderer\language::ZPL;

    static public function render(array|stdClass $data, int|null $printerId = null) : string|null
    {
        global $database;
        global $api;

        if(!is_array($data))$api->returnParameterError("Data");

        $printer = self::printer($printerId);
        
        $template = <<< STR
^XA
^PW300
^BY2,3,118^FT68,556^BCR,,N,N
^FDBarcode^FS
^FT116,21^A0R,42,40^FH\^FDMPN^FS
^FT168,21^A0R,42,40^FH\^FDMfr^FS
^FT213,575^A0R,75,72^FH\^FDStockId^FS
^FT213,21^A0R,75,72^FH\^FDPartNo^FS
^FT20,21^A0R,42,19^FH\^FDDescription^FS
^FT64,21^A0R,42,40^FH\^FDMovement^FS
^PQ1,0,1,Y^XZ
STR;

        $items = [];
        foreach ($data as $item){
            $stockNumber = barcodeParser_StockNumber($item);
            $historyItem = barcodeParser_StockHistoryNumber($item);
            $items[] = $stockNumber."-".$historyItem;
        }

        global $companyName;

        $itemListString = "'".implode("','",$items)."'";
        $query = <<< STR
            SELECT 
                vendor_displayName(supplier.Id) AS SupplierName, 
                supplierPart.SupplierPartNumber, 
                partStock.StockNumber, 
                vendor_displayName(manufacturer.Id) AS ManufacturerName, 
                partStock.LotNumber,
                manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
                partStock.Date, 
                manufacturerPart_partNumber.Description,
                partStock_history.CreationDate,
                partStock_history.Cache_ChangeIndex AS ChangeIndex,
                partStock_history.ChangeType,
                partStock_history.Quantity,
                partStock_history.Note,
                workOrder.WorkOrderNumber,
                workOrder.Name AS WorkOrderName,
                productionPart.Number AS ProductionPartNumber,
                productionPart.Description AS ProductionPartDescription
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
            LEFT JOIN (SELECT Id, vendor_displayName(id) FROM vendor)manufacturer ON manufacturer.Id = manufacturerPart_item.VendorId OR manufacturer.Id = manufacturerPart_partNumber.VendorId OR manufacturer.Id = manufacturerPart_series.VendorId
            LEFT JOIN (SELECT Id, vendor_displayName(id) FROM vendor)supplier ON supplier.Id = supplierPart.VendorId
            LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
            LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
            WHERE CONCAT(partStock.StockNumber,"-",partStock_history.Cache_ChangeIndex ) IN ($itemListString)
            GROUP BY partStock_history.Id
        STR;

        $stockHistoryItems = $database->query($query);

        if(count($stockHistoryItems) === 0){
            $api->returnError("Item not found");
        }
        
        foreach($stockHistoryItems as $item)
        {
            $code = $template;
            
            $item->TypeDescription = "";
            if($item->ChangeType == "Create"){
                $item->TypeDescription = "Create";
            }else if($item->ChangeType == 'Absolute') {
                $item->TypeDescription = "Stocktaking";
            }else if($item->ChangeType == 'Relative') {
                if($item->Quantity > 0 ){
                    $item->TypeDescription = "Add";
                }else {
                    $item->TypeDescription = "Remove";
                    $item->Quantity = abs($item->Quantity);
                }
            }
            
            $itemCode = barcodeFormatter_StockHistoryNumber($item->StockNumber, $item->ChangeIndex);
            $movement = $item->TypeDescription." ".$item->Quantity." pcs;".$item->Note;
            
            
            $code = str_replace('Barcode', $itemCode??"", $code);
            $code = str_replace('StockId', $item->StockNumber."-".$item->ChangeIndex, $code);
            $code = str_replace('Mfr', $item->ManufacturerName??"", $code);
            $code = str_replace('MPN', $item->ManufacturerPartNumber??"", $code);
            $code = str_replace('PartNo',$item->ProductionPartNumber??"", $code);
            $code = str_replace('Movement', $movement, $code);
            $code = str_replace('Description', $item->ProductionPartDescription??"", $code);
            
            //echo $code;
            
            
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket === false) $api->returnError( "Printer connection failed: ".socket_strerror(socket_last_error()) );
            
            $connection = socket_connect($socket, $printer->Ip, $printer->Port);
            if ($connection === false) $api->returnError( "Printer connection failed: ".socket_strerror(socket_last_error($socket)) );
            
            socket_write($socket, $code, strlen($code));
            
            socket_close($socket);

        }
        
        return null;
    }
}
