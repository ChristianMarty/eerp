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
	
	if(!isset($data['PrinterId'])) sendResponse(null, "Printer ID missing");
	
	$printerId = intval($data['PrinterId']);
	
	if(isset($data['Driver']))$driver = $data['Driver'];
	else $driver = "raw";
	
	if(isset($data['Language'])) $language = $data['Language'];
	else $language = "";
	
	$data = $data['Data'];
	
	$query = "SELECT * FROM printer WHERE Id =".$printerId;
	
	$result = dbRunQuery($dbLink,$query);
	
	$printer = mysqli_fetch_assoc($result);
	
	$output = array();
	$output['Printer'] = $printer;


	if(strtoupper($language) == 'ESCPOS')
	{	
		$data =  mb_convert_encoding($data, "ASCII");
		$parts = explode('\\', $data);
		if(strlen($parts[0]) == 0) unset($parts[0]);
		
		$data = "";
		foreach($parts as $part)
		{
			if(strtolower(substr($part, 0,1)) === "x")
			{
				$data .= hex2bin(substr($part,1,2));
				if(strlen($part) > 3) $data .= substr($part,3);
			}
			else
			{
				$data .= "\\".$part;
			}
		}
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
	
	dbClose($dbLink);
	sendResponse($output);

}
?>