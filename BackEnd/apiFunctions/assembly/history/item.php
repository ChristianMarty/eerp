<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/assembly/history/
// Author   : Christian Marty
// Date		: 21.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["AssemblyHistoryId"])) sendResponse(Null,"AssemblyHistoryId not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$assemblyHistoryId = dbEscapeString($dbLink, $_GET["AssemblyHistoryId"]);

	$query  = "SELECT * FROM assembly_item_history ";
	$query .= "LEFT JOIN assembly_item ON assembly_item.Id = assembly_item_history.AssemblyItemId ";	
	$query .= "WHERE assembly_item_history.Id = {$assemblyHistoryId}";	

	
	$result = dbRunQuery($dbLink,$query);
	
	$history = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$assembly = $r;
		$temp = array();
		$temp['Title'] = $r['Title'];
		$temp['Description'] = $r['Description'];
		$temp['SerialNumber'] = $r['SerialNumber'];
		$temp['Barcode'] = "ASM-".$r['AssemblyItemNo'];
		$temp['EditToken'] = $r['EditToken'];
		if($r['Data'] != NULL) $temp['Data'] = json_decode($r['Data']);
		else $temp['Data'] = NULL;
		$temp['Date'] = $r['Date'];
		
		$history = $temp;
	}
	
	dbClose($dbLink);	
	sendResponse($history);
}
else if($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["EditToken"])) sendResponse(Null,"EditToken not set");
	
	
	$jsonData = null;
	if(isset($data['Data']))
	{
		$jsonData = trim($data['Data']);
		json_decode($jsonData);
		if(json_last_error() !== JSON_ERROR_NONE)sendResponse(null,"Data is not valid JSON");
	}
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$token = dbEscapeString($dbLink,$data["EditToken"]);

	$sqlData = array();
	$sqlData['Title'] = dbEscapeString($dbLink,$data['Title']);
	$sqlData['Description'] = dbEscapeString($dbLink,$data['Description']);
	$sqlData['Data']['raw'] = "JSON_UNQUOTE('".dbEscapeString($dbLink,$jsonData)."')";
	$query = dbBuildUpdateQuery($dbLink,"assembly_item_history", $sqlData, 'EditToken = "'.$token.'"');
	
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if($result == false) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["AssemblyItemNo"])) sendResponse(Null,"AssemblyItemNo not set");
	
	$jsonData = null;
	if(isset($data['Data']))
	{
		$jsonData = trim($data['Data']);
		json_decode($jsonData);
		if(json_last_error() !== JSON_ERROR_NONE)sendResponse(null,"Data is not valid JSON");
	}
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$assemblyNo = dbEscapeString($dbLink,$data['AssemblyItemNo']);
	$assemblyNo = strtolower($assemblyNo);
	$assemblyNo = str_replace("asm-","",$assemblyNo);
	
	$sqlData = array();
	$sqlData['Title'] = dbEscapeString($dbLink,$data['Title']);
	$sqlData['Description'] = dbEscapeString($dbLink,$data['Description']);
	$sqlData['Data']['raw'] = "JSON_UNQUOTE('".dbEscapeString($dbLink,$jsonData)."')";
	$sqlData['AssemblyItemId']['raw'] = "(SELECT Id FROM assembly_item WHERE AssemblyItemNo = '".$assemblyNo."' )";
	$sqlData['EditToken']['raw'] = "history_generateEditToken()";
	$query = dbBuildInsertQuery($dbLink,"assembly_item_history", $sqlData);
	
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if($result == false) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
?>