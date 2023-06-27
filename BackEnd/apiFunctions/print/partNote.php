<?php
//*************************************************************************************************
// FileName : partNote.php
// FilePath : apiFunctions/print/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/escpos/autoload.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	
	global $companyName;
	
	$items = $data['Items'];
	$printerId = intval($data['PrinterId']);
    $workOrderNumber =  barcodeParser_WorkOrderNumber($data['WorkOrderNumber']);

	$query = "SELECT * FROM printer WHERE Id =".$printerId;
	$result = dbRunQuery($dbLink,$query);	
	$printer = mysqli_fetch_assoc($result);
	
	$workOrder = null;
	if($workOrderNumber != 0)
	{
		$query = "SELECT * FROM workOrder WHERE WorkOrderNumber =".$workOrderNumber;
		$result = dbRunQuery($dbLink,$query);	
		$workOrder = mysqli_fetch_assoc($result);
	}

	$connector = new NetworkPrintConnector($printer['Ip'], $printer['Port']);
	$printer = new Printer($connector);
	
	$lineLength = 42;
	$printer -> initialize();
	
	if($items != null)
	{
		foreach($items as $key => $line)
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
				
				$printer -> text(barcodeFormatter_WorkOrderNumber($workOrder['WorkOrderNumber'])." - ".$workOrder['Title']."\n");
				$printer -> feed(1);
			}
			
			$printer -> selectPrintMode(Printer::MODE_FONT_A);
			$printer -> setTextSize(2, 2);
			$printer -> text($line['Barcode']."\n");
			$printer -> feed(1);
			
			$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
			$printer -> text('Quantity : ');
			$printer -> selectPrintMode(Printer::MODE_FONT_A);
			$printer -> text(number_format($line['RemoveQuantity'])."\n");
			$printer -> feed(1);
			
			$printer -> setJustification(Printer::JUSTIFY_LEFT);
			
			
			$printer -> text($line['ManufacturerName']." ".$line['ManufacturerPartNumber']."\n");
			
	
			if(isset($line['Note']) && $line['Note'] != null && $line['Note'] != "")
			{
				$printer -> feed(1);
				$printer -> text($line['Note']."\n");
			}
			
			$printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
			
			$printer -> feed(1);
			$printer -> setBarcodeHeight(80);
			$printer -> barcode($line['Barcode']);
			
			$printer -> text(str_repeat("-",$lineLength)."\n");
			$printer -> setJustification(Printer::JUSTIFY_CENTER);
			$printer -> text(date("Y-m-d H:i:s")."\n");

			$printer -> cut();
		}
	}
	
	$printer -> close();
	
	$output = array();
	
	dbClose($dbLink);
	sendResponse($output);
}
?>