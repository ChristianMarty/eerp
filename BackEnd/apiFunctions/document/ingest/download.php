<?php
//*************************************************************************************************
// FileName : download.php
// FilePath : apiFunctions/document/ingest/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../../config.php";

if($api->isPost())
{
    $data = $api->getPostData();
    $url = $data->url;

    $fileName = basename($url);
    $file = file_get_contents ($url);
    if (!$file){
        $api->returnError("File download failed!");
    }

	$output = array();
	$error = null;
	
	global $serverDataPath;
	global $ingestPath;

	// Check if file already exists
	$fileMd5 = md5($file);
	
	$query = "SELECT * FROM `document` WHERE `Hash`='".$fileMd5."'";
	$result = $database->query($query);

	if(count($result) == 0)
	{
        file_put_contents($serverDataPath.$ingestPath."/".$fileName, $file);
		$output["message"]= "File downloaded successfully.";
	}
	else
	{
		$output["message"]= "The downloaded file already exists.";
		$error= "The downloaded file already exists.";
	}	

	$output["fileInfo"]= $result;

    $api->returnData($output);
}