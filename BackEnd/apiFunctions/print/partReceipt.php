<?php
//*************************************************************************************************
// FileName : partReceipt.php
// FilePath : apiFunctions/print/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/escpos/autoload.php";

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	global $companyName;
	
	$printData = $data['Data'];
	$printerId = intval($data['PrinterId']);
	$workOrderNo = intval($printData['WorkOrderNo']);

	$query = "SELECT * FROM printer WHERE Id =".$printerId;
	$result = dbRunQuery($dbLink,$query);	
	$printer = mysqli_fetch_assoc($result);
	
	$workOrder = null;
	if($workOrderNo != 0)
	{
		$query = "SELECT * FROM workOrder WHERE WorkOrderNo =".$workOrderNo;
		$result = dbRunQuery($dbLink,$query);	
		$workOrder = mysqli_fetch_assoc($result);
	}

	$connector = new NetworkPrintConnector($printer['Ip'], $printer['Port']);
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
		$printer -> text("WO-".$workOrder['WorkOrderNo']." - ".$workOrder['Title']."\n");
		$printer -> feed(1);
	}
	
	$lineLength = 42;
	
	if($printData['Items'] != null)
	{
		foreach($printData['Items'] as $key => $line)
		{
			$str1 = $line['Barcode']." ";
			$len1 = strlen($str1);
			$str2 = " ".number_format($line['RemoveQuantity']);
			$len2 = " ".strlen($str2);
			
			$str = str_repeat("-",$lineLength-($len1+$len2)).$str2;
			
			$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
			$printer -> setJustification(Printer::JUSTIFY_LEFT);
			$printer -> setTextSize(1, 1);
			$printer -> text($str1);
			$printer -> selectPrintMode(Printer::MODE_FONT_A);
			$printer -> text($str."\n");
			
			if(isset($line['Note']))$line['Note'] = trim($line['Note']);
			else $line['Note'] = null;
			
			if($line['Note'] != null && $line['Note'] != "")
			{
				$printer -> selectPrintMode(Printer::MODE_FONT_A);
				$printer -> setJustification(Printer::JUSTIFY_LEFT);
				$printer -> text("-> ".$line['Note']."\n");
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
	
	dbClose($dbLink);
	sendResponse($output);
}
?>