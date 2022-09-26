<?php
//*************************************************************************************************
// FileName : assemblyBonPrint.php
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
	
	$printData = $data['data'];
	$printerId = intval($data['PrinterId']);
	
	$query = "SELECT * FROM printer WHERE Id =".$printerId;
	
	$result = dbRunQuery($dbLink,$query);
	
	$r = mysqli_fetch_assoc($result);

	$connector = new NetworkPrintConnector($r['Ip'], $r['Port']);
	$printer = new Printer($connector);
	
	$printer -> initialize();
	$printer -> selectPrintMode(Printer::MODE_FONT_B);
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> setTextSize(2, 2);
	$printer -> text($companyName."\n");
	$printer -> feed(1);
	
	$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
	$printer -> setTextSize(1, 1);
	$printer -> text($printData['Title']."\n");
	$printer -> selectPrintMode(Printer::MODE_FONT_A);
	$printer -> text($printData['Description']."\n");
	$printer -> feed(1);
	
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	
	$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
	$printer -> text("SN: ");
	$printer -> selectPrintMode(Printer::MODE_FONT_A);
	$printer -> text($printData['SerialNumber']."\n");
	
	$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
	$printer -> text("Date: ");
	$printer -> selectPrintMode(Printer::MODE_FONT_A);
	$printer -> text($printData['Date']."\n");
	
	$printer -> feed(1);
	
	if($printData['Data'] != NULL)
	{
		foreach($printData['Data'] as $key => $line)
		{
			$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
			$printer -> text($key.": ");
				
			if(is_array($line))
			{
				$printer -> text("\n");
				foreach($line as $key => $line)
				{
					
					$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
					$printer -> text("  ".$key.": ");
					$printer -> selectPrintMode(Printer::MODE_FONT_A);
					$printer -> text($line."\n");
				}
			
			}
			else
			{
				$printer -> selectPrintMode(Printer::MODE_FONT_A);
				$printer -> text($line."\n");
			}
		}
	}
	
	$printer -> feed(1);
	$printer -> setBarcodeHeight(80);
	$printer->setBarcodeTextPosition(Printer::BARCODE_TEXT_BELOW);
	$printer -> barcode($printData['Barcode']);

	$printer -> cut();
	$printer -> close();
	
	$output = array();
	
	dbClose($dbLink);
	sendResponse($output);
}
?>