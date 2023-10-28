<?php
//*************************************************************************************************
// FileName : documentHash.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

$title = "Hash Documents";
$description = "Calculate missing document hash.";

global $api;
if($api->isGet("process.run"))
{
	$dbLink = dbConnect();
	
	$query = "SELECT  `Id`, `Path`, `Type`, `Hash` FROM `document` WHERE `Hash` IS NULL";
	$queryResult = dbRunQuery($dbLink,$query);
	
	global $serverDataPath;
	global $documentPath;
	
	$output = array();
	
	while($doc = mysqli_fetch_assoc($queryResult))
	{
		$filePath = $serverDataPath.$documentPath."/".$doc['Type']."/".$doc['Path'];

		$fileMd5 = md5_file ($filePath);
		
		$doc['ServerPath'] = $filePath;
		$doc['Hash'] = $fileMd5;
		
		$updateQuery = "UPDATE document SET Hash='".$fileMd5."' WHERE  Id=".$doc['Id'];
		dbRunQuery($dbLink,$updateQuery);
		
		$output[] = $doc;
	}

	dbClose($dbLink);
	$api->returnData($output);
}
