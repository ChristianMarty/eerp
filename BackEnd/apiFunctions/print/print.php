<?php
//*************************************************************************************************
// FileName : print.php
// FilePath : apiFunctions/print
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isPost())
{
    $data = $api->getPostData();
    if(!isset($data->Data)) $api->returnParameterMissingError("Data");
    if(!isset($data->PrinterId)) $api->returnParameterMissingError("PrinterId");
    $printerId = intval($data->PrinterId);
    if($printerId == 0) $api->returnParameterError("PrinterId");

    if(isset($data->Driver))$driver = $data->Driver;
    else $driver = "raw";

    if(isset($data->Language)) $language = $data->Language;
    else $language = "";


    $query = "SELECT * FROM printer WHERE Id ='$printerId' LIMIT 1;";
    $printer = $database->query($query)[0];


	$output = array();
	$output['Printer'] = $printer;
    $data = $data->Data;

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
		if ($socket === false) $api->returnError( "Printer connection failed: ".socket_strerror(socket_last_error()) );
		
		$connection = socket_connect($socket, $printer->Ip, $printer->Port);
		if ($connection === false) $api->returnError( "Printer connection failed: ".socket_strerror(socket_last_error($socket)) );
		
		socket_write($socket, $data, strlen($data));
		
		socket_close($socket);	
	}
	
	$api->returnData($output);
}
