<?php
//*************************************************************************************************
// FileName : print.php
// FilePath : apiFunctions/print
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";


if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$data = json_decode(file_get_contents('php://input'),true);
	
	$driver = $data['Driver'];
	$language = $data['Language'];
	$printerId = intval($data['PrinterId']);
	$data = $data['Data'];
	
	$query = "SELECT * FROM printer WHERE Id =".$printerId;
	
	$result = dbRunQuery($dbLink,$query);
	
	$printer = mysqli_fetch_assoc($result);
	
	$output = array();
	$output['Printer'] = $printer;
	
	if($language == 'ESCPOS')
	{
		$data =  mb_convert_encoding($data, "ASCII");
		
	}
	
	if($driver == 'raw')
	{
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) sendResponse($output, "Printer connection failed: ".socket_strerror(socket_last_error()) );
		
		$connection = socket_connect($socket, $printer['Ip'], $printer['Port']);
		if ($connection === false) sendResponse($output, "Printer connection failed: ".socket_strerror(socket_last_error($socket)) );
		
		socket_write($socket, $data, strlen($data));
		
		socket_close($socket);
		
	}

	sendResponse($output);

}
?>