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
require_once __DIR__ . "/../_dataset/stock.php";

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
        global $api;
        global $companyName;

        if(!is_array($data))$api->returnParameterError("Data");

        $stockData = \renderer\Stock::getData($data);
        if($stockData === null){
            $api->returnError("Item not found");
        }

        $printer = self::printer($printerId);
        $connector = new NetworkPrintConnector($printer->Ip, $printer->Port);
        $printer = new Printer($connector);

        $lineLength = 42;
        $printer -> initialize();

        foreach($stockData as $item)
        {
            $printer -> selectPrintMode(Printer::MODE_FONT_B);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> setTextSize(2, 2);
            $printer -> text($companyName."\n");
            $printer -> feed(1);

            $printer -> selectPrintMode(Printer::MODE_FONT_A);
            if($item->workOrderNumber !== null)
            {
                $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
                $printer -> setTextSize(1, 1);
                $printer -> text("Work Order: ");

                $printer -> text($item->workOrderNumber." - ".$item->workOrderName."\n");
                $printer -> feed(1);
            }

            if(!empty($item->productionPartNumber)) {
                $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
                $printer->setTextSize(3, 3);
                foreach ($item->productionPartNumber as $productionPartNumber) {
                    $printer->text($productionPartNumber . "\n");
                }
                $printer->feed(1);
            }

            $printer -> selectPrintMode(Printer::MODE_FONT_A);
            $printer -> setTextSize(2, 2);
            $printer -> text($item->itemCode."\n");
            $printer -> feed(1);

            $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer -> text($item->typeDescription.' Quantity : ');
            $printer -> selectPrintMode(Printer::MODE_FONT_A);
            $printer -> text(number_format($item->quantity)."\n");
            $printer -> feed(1);

            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text($item->manufacturerName."\n");
            $printer -> text($item->manufacturerPartNumber."\n");
            $printer -> feed(1);

            if($item->partDescription !== null)
            {
                $printer -> text($item->partDescription."\n");
                $printer -> feed(1);
            }

            if($item->note !== null)
            {
                $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
                $printer -> text("Note:\n");
                $printer -> selectPrintMode(Printer::MODE_FONT_A);
                $printer -> text($item->note."\n");
                $printer->feed(1);
            }

            $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
            $printer->setBarcodeHeight(80);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->barcode($item->itemCode, Printer::BARCODE_CODE93);

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(str_repeat("-",$lineLength)."\n");
            $printer->text(date("Y-m-d H:i:s")."\n");

            $printer->cut();
        }

        $printer->close();

        return null;
    }
}
