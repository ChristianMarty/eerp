<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/document/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{

	if(isset($_GET["DocId"]))
	{
		$DocId = $_GET["DocId"];
	}
	else
	{
		sendResponse($output,"No Document Item Specified");
	}
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$DocId = dbEscapeString($dbLink, $DocId );
	
	$query = "SELECT * FROM `document` WHERE `Id` = '".$DocId."' ";
	
	$output = array();

	$result = dbRunQuery($dbLink,$query);
	$r = mysqli_fetch_assoc($result);
	
	$id = $r['Id'];
	unset($r['Id']);
	$output = $r;
	
	dbClose($dbLink);	
	sendResponse($output);
	
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$output = array();
	$error = null;
	
	global $serverDataPath;
	global $documentPath;
	
	$fileName = basename($_FILES["file"]["name"]);
	$fileDir  = $serverDataPath.$documentPath."/";
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
		$dbPath = dbEscapeString($dbLink,$fileName);
		$dbFileHash = dbEscapeString($dbLink,$fileMd5);
		$dbFileDescription = dbEscapeString($dbLink,$_POST["Description"]);
		$dbFileType = dbEscapeString($dbLink,$_POST["Type"]);
		
		$query = "INSERT INTO `document` (`Path`,`Type`,`Description`,`Hash`)";
		$query .= "VALUES ('".$dbPath."', '".$dbFileType."', '".$dbFileDescription."', '".$dbFileHash."')";
		

		if(dbRunQuery($dbLink,$query)) 
		{
			move_uploaded_file($file, $fileDir.$_POST["Type"]."/".$fileName);
		}
		else
		{
			$dbError = mysqli_error($dbLink);
			dbClose($dbLink);	
			sendResponse($output,$dbError);
		}
		
		$query = "SELECT * FROM `document` WHERE `Hash`='".$fileMd5."'";
		$result = dbRunQuery($dbLink,$query);
		if($result) 
		{
			$result = mysqli_fetch_assoc($result);
		}
		
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