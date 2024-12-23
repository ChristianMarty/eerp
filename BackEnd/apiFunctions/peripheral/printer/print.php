<?php
//*************************************************************************************************
// FileName : print.php
// FilePath : apiFunctions/peripheral/printer
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../renderer/_item/_renderer.php";

if($api->isPost())
{
    $data = $api->getPostData();
    if(!isset($data->Data)) $api->returnParameterMissingError("Data");
    if(!isset($data->PrinterId)) $api->returnParameterMissingError("PrinterId");
    if(!isset($data->RendererId)) $api->returnParameterMissingError("RendererId");

    $printerId = intval($data->PrinterId);
    if($printerId == 0) $api->returnParameterError("PrinterId");

    $rendererId = intval($data->RendererId);
    if($rendererId == 0) $api->returnParameterError("RendererId");

    $query = <<<QUERY
        SELECT 
            * 
        FROM peripheral 
        WHERE Id ='$printerId' LIMIT 1;
    QUERY;
    $printer = $database->query($query);
    if(count($printer) === 0){
        $api->returnParameterError("PrinterId not found");
    }
    $printer = $printer[0];

    $query = <<<QUERY
        SELECT 
            * 
        FROM renderer
        LEFT JOIN renderer_dataset ON renderer.DatasetId = renderer_dataset.Id
        WHERE renderer.Id ='$rendererId' LIMIT 1;
    QUERY;

    $renderer = $database->query($query);
    if(count($renderer) === 0){
        $api->returnParameterError("RendererId not found");
    }
    $renderer = $renderer[0];

    if(isset($data->Driver))$driver = $data->Driver;
    else $driver = "raw";

    if(isset($data->Language)) $language = $data->Language;
    else $language = "";

    $output = array();
	$output['Printer'] = $printer;
    $data = $data->Data;
    $printCode = "";

    if($renderer->Render == "Template"){
        $template = $renderer->Code;
        foreach ($data as $key => $value){
            if($key!==null) {
                $template = str_replace($key, $value??"", $template);
            }
        }

        foreach (explode(",", $renderer->Variables) as $key){ // replace unused keys with nothing
            $template = str_replace($key, "", $template);
        }

        $printCode = $template;
    }else if($renderer->Render == "PHP"){
        $rendererClassName = $renderer->Code;
        $filePath = __DIR__ . '/../../renderer/_item/' .$rendererClassName.'.php';
        if (!file_exists($filePath)) {
            $api->returnError("The requested renderer class does not exist");
        }
        require_once($filePath);
        $rendererClass = new $rendererClassName();
        $rendererClass->render($data, $printer->Id);
        $api->returnEmpty();
    }

	/*if(strtoupper($language) == 'ESCPOS')
	{
        $printCode =  mb_convert_encoding($printCode, "ASCII");
		$parts = explode('\\', $printCode);
		if(strlen($parts[0]) == 0) unset($parts[0]);

        $printCode = "";
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
	}*/
	
	if($driver == 'raw')
	{
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) $api->returnError( "Printer connection failed: ".socket_strerror(socket_last_error()) );
		
		$connection = socket_connect($socket, $printer->Ip, $printer->Port);
		if ($connection === false) $api->returnError( "Printer connection failed: ".socket_strerror(socket_last_error($socket)) );
		
		socket_write($socket, $printCode, strlen($printCode));
		
		socket_close($socket);	
	}
	
	$api->returnData($output);
}
