<?php
//*************************************************************************************************
// FileName : bonPrint.php
// FilePath : apiFunctions/print
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../util/escpos/autoload.php";
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

if($api->isPost())
{
    $data = $api->getPostData();
    if(!isset($data->Data)) $api->returnParameterMissingError("Data");
    if(!isset($data->PrinterId)) $api->returnParameterMissingError("PrinterId");
    $printerId = intval($data->PrinterId);
    if($printerId == 0) $api->returnParameterError("PrinterId");

	$query = "SELECT * FROM peripheral WHERE Id ='$printerId' LIMIT 1;";
    $printer = $database->query($query)[0];

    $connector = new NetworkPrintConnector($printer->Ip, $printer->Port);
    $printer = new Printer($connector);
	
	$printer -> initialize();
	foreach($data->data as $line)
	{
		$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
		$text = $line['PartNo']." - ".$line['Value']."\n";
		$printer -> text($text);
		
		$printer -> selectPrintMode(Printer::MODE_FONT_A);
		$text = $line['RefDes']."\n";
		$printer -> text($text);
	}

	$printer -> cut();
	$printer -> close();
	
	$api->returnEmpty();

}
