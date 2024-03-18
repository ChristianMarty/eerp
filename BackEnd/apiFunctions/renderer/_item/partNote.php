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


class partNote extends \renderer\renderer
{
    protected ?\renderer\language $method = \renderer\language::ESCPOS;

    static public function render(\stdClass $input) : string|null
    {
        global $database;
        global $api;

        if(!isset($input->Items)) $api->returnParameterMissingError("Items");
        if(!isset($input->PrinterId)) $api->returnParameterMissingError("PrinterId");
        $printerId = intval($input->PrinterId);
        if($printerId == 0) $api->returnParameterError("PrinterId");

        $query = "SELECT * FROM peripheral WHERE Id ='$printerId' LIMIT 1;";
        $printer = $database->query($query)[0];

        global $companyName;

        $workOrder = null;
        if(isset($data->WorkOrderNumber))
        {
            $workOrderNumber =  barcodeParser_WorkOrderNumber($data->WorkOrderNumber);
            if($workOrderNumber !== null)
            {
                $query = "SELECT WorkOrderNumber, Name FROM workOrder WHERE WorkOrderNumber ='$workOrderNumber' LIMIT 1;";
                $workOrder = $database->query($query)[0] ?? null;
            }
        }

        $connector = new NetworkPrintConnector($printer->Ip, $printer->Port);
        $printer = new Printer($connector);

        $lineLength = 42;
        $printer -> initialize();

        if($data->Items != null)
        {
            foreach($data->Items as $key => $line)
            {
                $printer -> selectPrintMode(Printer::MODE_FONT_B);
                $printer -> setJustification(Printer::JUSTIFY_CENTER);
                $printer -> setTextSize(2, 2);
                $printer -> text($companyName."\n");
                $printer -> feed(1);

                $printer -> selectPrintMode(Printer::MODE_FONT_A);
                if($workOrder != null)
                {
                    $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
                    $printer -> setTextSize(1, 1);
                    $printer -> text("Work Order: ");

                    $printer -> text(barcodeFormatter_WorkOrderNumber($workOrder->WorkOrderNumber)." - ".$workOrder->Name."\n");
                    $printer -> feed(1);
                }

                $printer -> selectPrintMode(Printer::MODE_FONT_A);
                $printer -> setTextSize(2, 2);
                $printer -> text($line->StockCode."\n");
                $printer -> feed(1);

                $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
                $printer -> text('Quantity : ');
                $printer -> selectPrintMode(Printer::MODE_FONT_A);
                $printer -> text(number_format($line->Quantity)."\n");
                $printer -> feed(1);

                $printer -> setJustification(Printer::JUSTIFY_LEFT);


                $printer -> text($line->Part->ManufacturerName." ".$line->Part->ManufacturerPartNumber."\n");


                if(isset($line->Note) && $line->Note != null && $line->Note != "")
                {
                    $printer -> feed(1);
                    $printer -> text($line->Note."\n");
                }

                $printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);

                $printer -> feed(1);
                $printer -> setBarcodeHeight(80);
                $printer -> setJustification(Printer::JUSTIFY_CENTER);
                $printer -> barcode($line->ItemCode, Printer::BARCODE_CODE93);

                $printer -> text(str_repeat("-",$lineLength)."\n");
                $printer -> setJustification(Printer::JUSTIFY_CENTER);
                $printer -> text(date("Y-m-d H:i:s")."\n");

                $printer -> cut();
            }
        }
        $printer -> close();

        return null;
    }
}
