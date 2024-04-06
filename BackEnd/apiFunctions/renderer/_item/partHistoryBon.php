<?php
//*************************************************************************************************
// FileName : partNote.php
// FilePath : apiFunctions/print/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once "_renderer.php";

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

require_once __DIR__ . "/../../util/escpos/autoload.php";
use \Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use \Mike42\Escpos\Printer;


class partHistoryBon extends \renderer\renderer
{
    protected ?\renderer\language $method = \renderer\language::ESCPOS;

    static public function render(array|stdClass $data, int|null $printerId = null) : string|null
    {
        global $database;
        global $api;

        if(!is_array($data))$api->returnParameterError("Data");

        $printer = self::printer($printerId);

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
                workOrder.Name AS WorkOrderName
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
            WHERE CONCAT(partStock.StockNumber,"-",partStock_history.Cache_ChangeIndex ) IN ($itemListString)
        STR;

        $stockHistoryItems = $database->query($query);

        if(count($stockHistoryItems) === 0){
            $api->returnParameterError("Item not found");
        }

        $connector = new NetworkPrintConnector($printer->Ip, $printer->Port);
        $printer = new Printer($connector);

        $lineLength = 42;
        $printer -> initialize();

        foreach($stockHistoryItems as $item)
        {
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

            $printer -> selectPrintMode(Printer::MODE_FONT_B);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> setTextSize(2, 2);
            $printer -> text($companyName."\n");
            $printer -> feed(1);

            $printer -> selectPrintMode(Printer::MODE_FONT_A);
            if($item->WorkOrderNumber !== null)
            {
                $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
                $printer -> setTextSize(1, 1);
                $printer -> text("Work Order: ");

                $printer -> text(barcodeFormatter_WorkOrderNumber($item->WorkOrderNumber)." - ".$item->WorkOrderName."\n");
                $printer -> feed(1);
            }

            $printer -> selectPrintMode(Printer::MODE_FONT_A);
            $printer -> setTextSize(2, 2);
            $printer -> text($itemCode."\n");
            $printer -> feed(1);

            $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer -> text($item->TypeDescription.' Quantity : ');
            $printer -> selectPrintMode(Printer::MODE_FONT_A);
            $printer -> text(number_format($item->Quantity)."\n");
            $printer -> feed(1);

            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text($item->ManufacturerName."\n");
            $printer -> text($item->ManufacturerPartNumber."\n");

            if(isset($line->Note) && $line->Note != null && $line->Note != "")
            {
                $printer -> feed(1);
                $printer -> text($line->Note."\n");
            }

            $printer->feed(1);
            $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
            $printer->setBarcodeHeight(80);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->barcode($itemCode, Printer::BARCODE_CODE93);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(str_repeat("-",$lineLength)."\n");
            $printer->text(date("Y-m-d H:i:s")."\n");

            $printer->cut();
        }

        $printer->close();

        return null;
    }
}
