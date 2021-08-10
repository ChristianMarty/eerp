<?php
//*************************************************************************************************
// FileName : bomView.php
// FilePath : apiFunctions/bom
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";


// TODO: This is not working -> fix  it all

require 'BonPrinter/autoload.php';
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

$output = array();

try{
	$connector = new NetworkPrintConnector("192.168.1.9", 20108);
	$printer = new Printer($connector);
	$printer->initialize();
}catch(Exception $e) {
	sendResponse($output,"Printer Conection Faild!");
	exit;
} 

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	
	try {
		
		$data = json_decode(file_get_contents('php://input'),true);
		
		$BoM = $data['data'];

		if($BoM == null)
		{
			$printer -> close();
			sendResponse($output,"No Data");
			exit;
		}
		else if(count($BoM) == 0)
		{
			$printer -> close();
			sendResponse($output,"No Data");
			exit;
		}
		
		foreach ($BoM as $PartDataLine)
		{
			$partTitel = $PartDataLine["PartNo"]." ".$PartDataLine['Value'];
			$partRefdes = $PartDataLine['RefDes'];

			$printer->setEmphasis(true);
			$printer->text($partTitel."\n");
			$printer->setEmphasis(false);
			$printer->text($partRefdes."\n");
		}
		
		$printer -> cut();
	}catch(Exception $e){
		$printer -> close();
		sendResponse($output,"Printer Error");
	}
	
	$printer -> close();
	sendResponse($output);
}

?>