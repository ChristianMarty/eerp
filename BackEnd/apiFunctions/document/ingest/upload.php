<?php
//*************************************************************************************************
// FileName : upload.php
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
	$output = array();
	$error = null;
	
	global $serverDataPath;
	global $ingestPath;
	
	$fileName = basename($_FILES["file"]["name"]);
	$file = $_FILES["file"]["tmp_name"];
	
	// Check if file already exists
	$fileMd5 = md5_file($file);
	
	$query = "SELECT * FROM `document` WHERE `Hash`='$fileMd5'";
	$result = $database->query($query);

    if(count($result) == 0) {
		move_uploaded_file($file, $serverDataPath.$ingestPath."/".$fileName);
		$output["message"]= "File uploaded successfully.";
	} else {
		$output["message"]= "The uploaded file already exists.";
		$error= "The uploaded file already exists.";
	}	

	$output["fileInfo"]= $result;
	
	$api->returnData($output);
}
