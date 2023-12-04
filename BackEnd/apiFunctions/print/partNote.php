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
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

require_once __DIR__ . "/../util/escpos/autoload.php";
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

if($api->isPost())
{
    $data = $api->getPostData();
    if(!isset($data->Items)) $api->returnParameterMissingError("Items");
    if(!isset($data->PrinterId)) $api->returnParameterMissingError("PrinterId");
    $printerId = intval($data->PrinterId);
    if($printerId == 0) $api->returnParameterError("PrinterId");

    $query = "SELECT * FROM printer WHERE Id ='$printerId' LIMIT 1;";
    $printer = $database->query($query)[0];

	global $companyName;

	$workOrder = null;
	if($data->WorkOrderNumber)
	{
		$workOrderNumber =  barcodeParser_WorkOrderNumber($data->WorkOrderNumber);
		if($workOrderNumber !== null)
		{
			$query = "SELECT * FROM workOrder WHERE WorkOrderNumber ='$workOrderNumber' LIMIT 1;";
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
				
				$printer -> text(barcodeFormatter_WorkOrderNumber($workOrder->WorkOrderNumber)." - ".$workOrder->Title."\n");
				$printer -> feed(1);
			}
			
			$printer -> selectPrintMode(Printer::MODE_FONT_A);
			$printer -> setTextSize(2, 2);
			$printer -> text($line->Barcode."\n");
			$printer -> feed(1);
			
			$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
			$printer -> text('Quantity : ');
			$printer -> selectPrintMode(Printer::MODE_FONT_A);
			$printer -> text(number_format($line->RemoveQuantity)."\n");
			$printer -> feed(1);
			
			$printer -> setJustification(Printer::JUSTIFY_LEFT);
			
			
			$printer -> text($line->ManufacturerName." ".$line->ManufacturerPartNumber."\n");
			
	
			if(isset($line->Note) && $line->Note != null && $line->Note != "")
			{
				$printer -> feed(1);
				$printer -> text($line->Note."\n");
			}
			
			$printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
			
			$printer -> feed(1);
			$printer -> setBarcodeHeight(80);
			$printer -> barcode($line->Barcode);
			
			$printer -> text(str_repeat("-",$lineLength)."\n");
			$printer -> setJustification(Printer::JUSTIFY_CENTER);
			$printer -> text(date("Y-m-d H:i:s")."\n");

			$printer -> cut();
		}
	}
	
	$printer -> close();
	
    $api->returnEmpty();
}
