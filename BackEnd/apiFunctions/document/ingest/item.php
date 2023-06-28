<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/document/ingest/
// Author   : Christian Marty
// Date		: 25.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../_functions.php";

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);

    $result = ingest($data);

    if(is_int($result)) sendResponse($result,null);
    else sendResponse(null,$result['error']);
}

else if($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	global $serverDataPath;
	global $ingestPath;
	
	if(!isset($data['FileName']) OR $data['FileName'] == "" OR $data['FileName'] == null) sendResponse(null,"File name is not set.");
	
	$src = $serverDataPath.$ingestPath."/".$data['FileName'];
	
	if (unlink($src)) 
	{
	  sendResponse(null,null);
	} 
	else 
	{
	  sendResponse(null,"File delete failed.");
	}	
}
?>