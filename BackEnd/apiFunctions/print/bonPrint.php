<?php
//*************************************************************************************************
// FileName : bonPrint.php
// FilePath : apiFunctions/print
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
	
	$lines = $data['Lines'];
	$printerId = intval($data['PrinterId']);
	
	$query = "SELECT * FROM supplier WHERE Id =".$printerId;
	
	$result = dbRunQuery($dbLink,$query);
	
	$r = mysqli_fetch_assoc($result);

	$connector = new NetworkPrintConnector($r['Ip'], $r['Port']);
	$printer = new Printer($connector);
	
	$printer -> initialize();
	foreach($lines as $line)
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
	
	$output = array();
	sendResponse($output);

}
?>