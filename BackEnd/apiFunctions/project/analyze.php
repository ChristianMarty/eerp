<?php
//*************************************************************************************************
// FileName : analyze.php
// FilePath : apiFunctions/project
// Author   : Christian Marty
// Date		: 02.01.2021
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/extractVariable.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$path = __dir__."/analyze/";
	$output = array();
	$files = scandir($path);
	$files = array_diff($files, array('.', '..'));
	
	foreach( $files as $file)
	{
		if(is_file($path.$file))
		{
			if(pathinfo($path.$file,PATHINFO_EXTENSION ) == "php")
			{
				array_push($output, getInfo("",$file));
			}				
			
		}
		else if(is_dir($path.$file))
		{
			$files2 = scandir($path.$file);
			$files = array_diff($files, array('.', '..'));
			
			foreach( $files2 as $file2)
			{
				if(pathinfo($path.$file."/".$file2,PATHINFO_EXTENSION ) == "php")
				{
					array_push($output, getInfo($file, $file2));
				}
			}
		}
	}
	sendResponse($output);
}

function getInfo($path, $file)
{
	global $apiRootPath;
	
	$filePath = __dir__."/analyze/".$path."/".$file;
	
	$output = array();
	$filename = pathinfo($filePath,PATHINFO_FILENAME);
	$output["FileName"] = $filename;
	
	$output["Title"] = extractVariable($filePath,"title");
	$output["Description"] = extractVariable($filePath,"description");
	$output["Path"] =  "/project/analyze/".$path."/".$filename;
	
	return $output;
}

?>