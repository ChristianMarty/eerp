<?php
//*************************************************************************************************
// FileName : list.php
// FilePath : apiFunctions/document/ingest/
// Author   : Christian Marty
// Date		: 25.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	global $serverDataPath;
	global $documentIngestPath;
	global $dataRootPath;
	
	$docs = scandir($serverDataPath.$documentIngestPath);
	
	$output = array();
	
	foreach($docs as $key => $line)
	{
		if($line == "." or $line == "..") continue;
		
		$tmp = array();
		$tmp["FileName"] = $line;
		$tmp["Date"] = "";
		$tmp["Size"] = "";
		$tmp["Path"] = $dataRootPath.$documentIngestPath."/".$line;
		
		array_push($output, $tmp); 
	}
	
	sendResponse($output);
}
?>