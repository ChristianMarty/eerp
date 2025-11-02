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
require_once __DIR__ . "/../_dataset/stock.php";

class partHistoryLabel extends \renderer\renderer
{
    protected ?\renderer\language $method = \renderer\language::ZPL;

    static public function render(array|stdClass $data, int|null $printerId = null) : string|null
    {
        global $api;

        if(!is_array($data))$api->returnParameterError("Data");

        $stockData = \renderer\Stock::getData($data);
        if($stockData === null){
            $api->returnError("Item not found");
        }

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

        foreach($stockData as $item)
        {
            $code = $template;
            $movement = $item->typeDescription." ".$item->quantity." pcs;".$item->note;
            
            $code = str_replace('Barcode', $item->itemCode, $code);
            $code = str_replace('StockId', $item->stockNumber."-".$item->changeIndex, $code);
            $code = str_replace('Mfr', $item->manufacturerName, $code);
            $code = str_replace('MPN', $item->manufacturerPartNumber, $code);
            $code = str_replace('PartNo',$item->productionPartNumber[0]??"", $code);
            $code = str_replace('Movement', $movement, $code);
            $code = str_replace('Description', $item->partDescription??"", $code);


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
