<?php
//*************************************************************************************************
// FileName : _functions.php
// FilePath : apiFunctions/document/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

function checkFileNotDuplicate($path)
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	// Check if file already exists
	$fileMd5 = md5_file($path);
	
	$query = "SELECT * FROM document WHERE Hash='".$fileMd5."'";

	$result = dbRunQuery($dbLink,$query);
	$existingFile = null;
	if($result) 
	{
		$existingFile = mysqli_fetch_assoc($result);
	}
	dbClose($dbLink);
	
	$retuning = array();
	
	if($existingFile != null)
	{
		$retuning['preexisting'] = true;
		$retuning['hash'] = $existingFile['Hash'];
		$retuning['path'] = $existingFile['Path'];
		$retuning['type'] = $existingFile['Type'];
		$retuning['description'] = $existingFile['Description'];
	}
	else 
	{
		$retuning['preexisting'] = false;
		$retuning['hash'] = $fileMd5;
	}
	
	return $retuning;
}

?>