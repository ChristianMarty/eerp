<?php
//*************************************************************************************************
// FileName : partReceipt.php
// FilePath : apiFunctions/renderer/_item
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

class partReceiptBon extends \renderer\renderer
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
            $items[] = barcodeParser_AssemblyUnitHistoryNumber($item);
        }

        $itemListString = "'".implode("','",$items)."'";
        $query = <<< STR
            SELECT 
                partStock.StockNumber,
                partStock_history.Cache_ChangeIndex AS ChangeIndex,
                partStock_history.ChangeType,
                partStock_history.Quantity,
                partStock_history.Note
                workOrder.WorkOrderNumber,
                workOrder.Name AS WorkOrderName
            FROM partStock_history
            LEFT JOIN partStock ON partStock_history.StockId = partStock.Id
            LEFT JOIN workOrder ON workOrder.Id = partStock_history.WorkOrderId
            WHERE CONCAT(partStock.StockNumber,"-",partStock_history.Cache_ChangeIndex ) IN ($itemListString)
        STR;
        $stockHistoryItems = $database->query($query);
        if(count($stockHistoryItems) === 0){
            $api->returnParameterError("Item not found");
        }

        global $companyName;

        $connector = new NetworkPrintConnector($printer->Ip, $printer->Port);
        $printer = new Printer($connector);

        $printer->initialize();
        $printer->selectPrintMode(Printer::MODE_FONT_B);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2, 2);
        $printer->text($companyName . "\n");
        $printer->feed(1);

        if ($stockHistoryItems[0]->WorkOrderNumber != null) {
            $wo = $stockHistoryItems[0];
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setTextSize(1, 1);
            $printer->text("Work Order: ");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text(barcodeFormatter_WorkOrderNumber($wo->WorkOrderNumber) . " - " . $wo->WorkOrderName . "\n");
            $printer->feed(1);
        }

        $lineLength = 42;
        foreach ($stockHistoryItems as $line) {
            $str1 = barcodeFormatter_StockHistoryNumber($line->StockNumber, $line->ChangeIndex)." ";
            $len1 = " " . strlen($str1);
            $str2 = " " . number_format($line->Quantity);
            $len2 = " " . strlen($str2);

            $str = str_repeat("-", $lineLength - ($len1 + $len2)) . $str2;

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setTextSize(1, 1);
            $printer->text($str1);
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text($str . "\n");

            if (isset($line->Note)) $line->Note = trim($line->Note);
            else $line->Note = null;

            if ($line->Note != null && $line->Note != "") {
                $printer->selectPrintMode(Printer::MODE_FONT_A);
                $printer->setJustification(Printer::JUSTIFY_LEFT);
                $printer->text("-> " . $line->Note . "\n");
            }
        }


        $printer->feed(1);
        $printer->selectPrintMode(Printer::MODE_FONT_A);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text(str_repeat("-",$lineLength)."\n");
        $printer->text(date("Y-m-d H:i:s")."\n");

        $printer->cut();
        $printer->close();

        return null;
    }
}
