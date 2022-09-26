<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/document/ingest/
// Author   : Christian Marty
// Date		: 25.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../_functions.php";

$fileNameIllegalCharactersRegex = '/[ :"*?<>|\\/]+/';

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data['Name']) OR $data['Name'] == "" OR $data['Name'] == null) sendResponse(null,"Name is not set.");
	if(!isset($data['Type']) OR $data['Type'] == "" OR $data['Type'] == null) sendResponse(null,"Type is not set.");
	if(!isset($data['FileName']) OR $data['FileName'] == "" OR $data['FileName'] == null) sendResponse(null,"File name is not set.");
	
	if(preg_match($fileNameIllegalCharactersRegex,$data['FileName']) != 0) sendResponse(null,'File name contains illegal character.');
	if(preg_match($fileNameIllegalCharactersRegex,$data['Name']) != 0) sendResponse(null,'File name contains illegal character.');
	
	global $serverDataPath;
	global $ingestPath;
	global $documentPath;
	
	
	$src = $serverDataPath.$ingestPath."/".$data['FileName'];
	$dstFileName = $data['Name'].".".pathinfo($src, PATHINFO_EXTENSION);
	
	$dst = $serverDataPath.$documentPath."/".$data['Type']."/".$dstFileName;
	
	if(!file_exists($src)) sendResponse(null,"File path invalid.");
	if(file_exists($dst)) sendResponse(null,"File name already exists.");
	
	$fileHashCheck = checkFileNotDuplicate($src);
	
	if($fileHashCheck['preexisting'] == true) 
	{
		sendResponse(null,"File already exists as ".$fileHashCheck['path']." with type ".$fileHashCheck['type']);
	}

	if(!rename($src, $dst)) sendResponse(null,"File copy faild.");

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$sqlData = array();
	$sqlData['Path'] = $dstFileName;
	$sqlData['Type'] = $data['Type'];
	$sqlData['Description']['raw'] = dbStringNull(dbEscapeString($dbLink,$data['Description']));
	$sqlData['LinkType'] = "Internal";
	$sqlData['Hash'] = $fileHashCheck['hash'];
	$sqlData['DocumentNumber']['raw'] = "(SELECT generateItemNumber())";
		
	$query = dbBuildInsertQuery($dbLink,"document", $sqlData);
	
	$result = dbRunQuery($dbLink,$query);

	dbClose($dbLink);	
	sendResponse(null,null);
}

else if($_SERVER['REQUEST_METHOD'] == 'DELETE')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	global $serverDataPath;
	global $ingestPath;
	
	if(!isset($data['FileName']) OR $data['FileName'] == "" OR $data['FileName'] == null) sendResponse(null,"File name is not set.");

	if(preg_match($fileNameIllegalCharactersRegex,$data['FileName']) != 0) sendResponse(null,'File name contains illegal character. [/]');
	
	$src = $serverDataPath.$ingestPath."/".$data['FileName'];
	
	if (unlink($src)) 
	{
	  sendResponse(null,null);
	} 
	else 
	{
	  sendResponse(null,"File delete faild.");
	}	
}
?>