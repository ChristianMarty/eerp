<?php
//*************************************************************************************************
// FileName : partReceipt.php
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

	$workOrder = null;
	if($data->WorkOrderNumber)
	{
		$workOrderNumber =  barcodeParser_WorkOrderNumber($data->WorkOrderNumber);
		if($workOrderNumber !== null)
		{
			$query = "SELECT * FROM workOrder WHERE WorkOrderNumber =".$workOrderNumber;
			$workOrder = $database->query($query)[0] ?? null;
		}
	}

    global $companyName;

	$connector = new NetworkPrintConnector($printer->Ip, $printer->Port);
	$printer = new Printer($connector);
	
	$printer -> initialize();
	$printer -> selectPrintMode(Printer::MODE_FONT_B);
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> setTextSize(2, 2);
	$printer -> text($companyName."\n");
	$printer -> feed(1);
	
	if($workOrder != null)
	{
		$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
		$printer -> setTextSize(1, 1);
		$printer -> text("Work Order: ");
		$printer -> selectPrintMode(Printer::MODE_FONT_A);
		$printer -> text(barcodeFormatter_WorkOrderNumber($workOrder->WorkOrderNumber)." - ".$workOrder->Title."\n");
		$printer -> feed(1);
	}
	
	$lineLength = 42;
	
	if($data->Items != null)
	{
		foreach($data->Items as $key => $line)
		{
			$str1 = $line->Barcode." ";
			$len1 = strlen($str1);
			$str2 = " ".number_format($line->RemoveQuantity);
			$len2 = " ".strlen($str2);
			
			$str = str_repeat("-",$lineLength-($len1+$len2)).$str2;
			
			$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
			$printer -> setJustification(Printer::JUSTIFY_LEFT);
			$printer -> setTextSize(1, 1);
			$printer -> text($str1);
			$printer -> selectPrintMode(Printer::MODE_FONT_A);
			$printer -> text($str."\n");
			
			if(isset($line->Note))$line->Note = trim($line->Note);
			else $line->Note = null;
			
			if($line->Note != null && $line->Note != "")
			{
				$printer -> selectPrintMode(Printer::MODE_FONT_A);
				$printer -> setJustification(Printer::JUSTIFY_LEFT);
				$printer -> text("-> ".$line->Note."\n");
			}
		}
	}
	
	$printer -> feed(1);
	$printer -> selectPrintMode(Printer::MODE_FONT_A);
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text(str_repeat("-",$lineLength)."\n");
	$printer -> text(date("Y-m-d H:i:s")."\n");

	$printer -> cut();
	$printer -> close();
	
	$output = array();
	
	$api->returnEmpty();
}
