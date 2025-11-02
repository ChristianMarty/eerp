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
require_once __DIR__ . "/../../location/_location.php";
require_once __DIR__ . "/../../util/escpos/autoload.php";
use \Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use \Mike42\Escpos\Printer;

class locationInventoryListBon extends \renderer\renderer
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
            $items[] = \Numbering\parser(\Numbering\Category::Location, $item);
        }

        $itemListString = "'".implode("','",$items)."'";
        $query = <<< STR
            SELECT 
                Id,
                Cache_DisplayName,
                LocationNumber
            FROM location
            WHERE location.LocationNumber IN ($itemListString)
        STR;
        $locationItems = $database->query($query);
        if(count($locationItems) === 0){
            $api->returnParameterError("Item not found");
        }

        global $companyName;

        $connector = new NetworkPrintConnector($printer->Ip, $printer->Port);
        $printer = new Printer($connector);

        $printer->initialize();
        $lineLength = 42;
        foreach ($locationItems as $line) {

            $printer->selectPrintMode(Printer::MODE_FONT_B);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setTextSize(2, 2);
            $printer->text($companyName . "\n");
            $printer->feed(1);

            $locationCode = \Numbering\format(\Numbering\Category::Location, $line->LocationNumber);

            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->setTextSize(2, 2);
            $printer->text($line->Cache_DisplayName . "\n");
            $printer->setTextSize(2, 1);
            $printer->text($locationCode . "\n");
            $printer->feed(1);

            $items = location_getItems($line->Id);

            $printer->setJustification(Printer::JUSTIFY_LEFT);

            foreach ($items as $item) {
                $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
                $printer->setTextSize(1, 1);
                $printer->text($item['Item']."\n");
                $printer->selectPrintMode(Printer::MODE_FONT_A);
                $printer->text($item['Description'] . "\n");
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
