<?php
//*************************************************************************************************
// FileName : assemblyBonPrint.php
// FilePath : apiFunctions/print/
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

	global $companyName;

    $data = $data->Data;
	$printData = $data->Data;

	$query = "SELECT * FROM peripheral WHERE Id = '$printerId' LIMIT 1";
	$printer = $database->query($query)[0];

	$connector = new NetworkPrintConnector($printer->Ip, $printer->Port);
	$printer = new Printer($connector);
	
	$printer -> initialize();
	$printer -> selectPrintMode(Printer::MODE_FONT_B);
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> setTextSize(2, 2);
	$printer -> text($companyName."\n");
	$printer -> feed(1);
	
	$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
	$printer -> setTextSize(1, 1);
	$printer -> text($data->Title."\n");
	$printer -> selectPrintMode(Printer::MODE_FONT_A);
	$printer -> text($data->Description."\n");
	$printer -> feed(1);

    $printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
    $printer -> text($data->Type."\n");

	$printer -> setJustification(Printer::JUSTIFY_LEFT);

	$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
	$printer -> text("SN: ");
	$printer -> selectPrintMode(Printer::MODE_FONT_A);
	$printer -> text($data->SerialNumber."\n");
	
	$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
	$printer -> text("Date: ");
	$printer -> selectPrintMode(Printer::MODE_FONT_A);
	$printer -> text($data->Date."\n");
	
	$printer -> feed(1);
	
	if($data->Data != NULL)
	{
		foreach($data->Data as $key => $line)
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
	$printer -> barcode($data->ItemCode);

	$printer -> cut();
	$printer -> close();

	$api->returnEmpty();
}
