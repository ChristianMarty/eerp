<?php
//*************************************************************************************************
// FileName : upload.php
// FilePath : apiFunctions/document/ingest/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$output = array();
	$error = null;
	
	global $serverDataPath;
	global $ingestPath;
	
	$fileName = basename($_FILES["file"]["name"]);

	$file = $_FILES["file"]["tmp_name"];
	
	// Check if file already exists
	$fileMd5 = md5_file ($file);
	
	$query = "SELECT * FROM `document` WHERE `Hash`='".$fileMd5."'";
	$result = dbRunQuery($dbLink,$query);
	
	if($result) 
	{
		$result = mysqli_fetch_assoc($result);
	}
	
	if(!isset($result))
	{
		move_uploaded_file($file, $serverDataPath.$ingestPath."/".$fileName);

		$output["message"]= "File uploaded successfully.";
	}
	else
	{
		$output["message"]= "The uploaded file already exists.";
		$error= "The uploaded file already exists.";
	}	

	$output["fileInfo"]= $result;
	
	dbClose($dbLink);	
	sendResponse($output,$error);
}
?>