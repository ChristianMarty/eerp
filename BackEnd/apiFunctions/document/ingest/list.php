<?php
//*************************************************************************************************
// FileName : list.php
// FilePath : apiFunctions/document/ingest/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

if($api->isGet(Permission::Document_Ingest_List))
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
		
		$output[] = $tmp;
	}

    $api->returnData($output);
}
