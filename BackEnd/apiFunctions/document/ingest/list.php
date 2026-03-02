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

require_once __DIR__ . "/../_document.php";

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
        $serverFilePath =  $serverDataPath.$ingestPath."/".$line;

        if(str_ends_with($line,"---external")){
            $path = file_get_contents($serverFilePath);
            $fileName = $path;
            $linkType = \Document\LinkType::External;
            $size = "";
        }else{
            $fileName = $line;
            $path = $dataRootPath.$ingestPath."/".$line;
            $linkType = \Document\LinkType::Internal;
            $size = filesize($serverFilePath);
        }

		$tmp = array();
		$tmp["FileName"] = $fileName;
		$tmp["Date"] = date("Y-m-d H:i", filectime($serverFilePath));
		$tmp["Size"] = $size;
		$tmp["Path"] = $path;
        $tmp['LinkType'] = $linkType;
		
		$output[] = $tmp;
	}

    $api->returnData($output);
}
