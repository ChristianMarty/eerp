<?php
//*************************************************************************************************
// FileName : download.php
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
    $data = json_decode(file_get_contents('php://input'),true);
    $url = $data['url'];

    $fileName = basename($url);
    $file = file_get_contents ($url);
    if (!$file){
        sendResponse(null,"File download failed!");
    }

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$output = array();
	$error = null;
	
	global $serverDataPath;
	global $ingestPath;

	
	// Check if file already exists
	$fileMd5 = md5($file);
	
	$query = "SELECT * FROM `document` WHERE `Hash`='".$fileMd5."'";
	$result = dbRunQuery($dbLink,$query);
	
	if($result) 
	{
		$result = mysqli_fetch_assoc($result);
	}
	
	if(!isset($result))
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
	
	dbClose($dbLink);	
	sendResponse($output,$error);
}


?>