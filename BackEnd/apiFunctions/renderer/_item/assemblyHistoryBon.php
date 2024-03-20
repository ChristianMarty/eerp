<?php
//*************************************************************************************************
// FileName : assemblyBonPrint.php
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


class assemblyHistoryBon extends \renderer\renderer
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
                AssemblyUnitHistoryNumber,
                Title,
                Description,
                Data,
                Type,
                Date,
                assembly_unit.SerialNumber
            FROM assembly_unit_history
            LEFT JOIN assembly_unit ON assembly_unit.Id = assembly_unit_history.AssemblyUnitId
            WHERE AssemblyUnitHistoryNumber IN ($itemListString)
        STR;
        $assemblyHistoryItems = $database->query($query);

        if(count($assemblyHistoryItems) === 0){
            $api->returnParameterError("Item not found");
        }

        $connector = new NetworkPrintConnector($printer->Ip, $printer->Port);
        $printer = new Printer($connector);

        global $companyName;

        $lineLength = 42;
        $printer -> initialize();

        foreach($assemblyHistoryItems as $item) {
            $itemCode = barcodeFormatter_AssemblyUnitHistoryNumber($item->AssemblyUnitHistoryNumber);
            $printer->selectPrintMode(Printer::MODE_FONT_B);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text($companyName . "\n");
            $printer->feed(1);

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setTextSize(1, 1);
            $printer->text($item->Title . "\n");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text($item->Description . "\n");
            $printer->feed(1);

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text($item->Type . "\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text("SN: ");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text($item->SerialNumber . "\n");

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text("Date: ");
            $printer->selectPrintMode(Printer::MODE_FONT_A);
            $printer->text($item->Date . "\n");

            $printer->feed(1);

            if ($item->Data != NULL) {
                $data = json_decode($item->Data);
                foreach ($data as $key => $line) {
                    $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
                    $printer->text($key . ": ");

                    if (is_array($line)) {
                        $printer->text("\n");
                        foreach ($line as $key => $line) {

                            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
                            $printer->text("  " . $key . ": ");
                            $printer->selectPrintMode(Printer::MODE_FONT_A);
                            $printer->text($line . "\n");
                        }
                    } else {
                        $printer->selectPrintMode(Printer::MODE_FONT_A);
                        $printer->text($line . "\n");
                    }
                }
            }

            $printer->feed(1);
            $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
            $printer->setBarcodeHeight(80);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->barcode($itemCode, Printer::BARCODE_CODE93);

            $printer->text(str_repeat("-",$lineLength)."\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text(date("Y-m-d H:i:s")."\n");

            $printer->cut();
        }

        $printer->close();

        return null;
    }
}
