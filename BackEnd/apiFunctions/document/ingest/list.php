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
	global $dataRootPath;
	global $ingestPath;
	
	$docs = scandir($serverDataPath.$ingestPath);
	
	$output = array();
	
	foreach($docs as $key => $line)
	{
		if($line == "." or $line == "..") continue;
		
		$tmp = array();
		$tmp["FileName"] = $line;
		$tmp["Date"] = "";
		$tmp["Size"] = "";
		//$tmp["Path"] = $dataRootPath.$documentIngestPath."/".$line;
		
		$tmp["Path"] = $dataRootPath.$ingestPath."/".$line;
		
		array_push($output, $tmp); 
	}
	
	sendResponse($output);
}
?>