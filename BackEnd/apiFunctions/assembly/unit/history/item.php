<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/assembly/unit/history/
// Author   : Christian Marty
// Date		: 21.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";
require __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../../../util/_json.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["AssemblyHistoryId"])) sendResponse(Null,"AssemblyHistoryId not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$assemblyHistoryId = dbEscapeString($dbLink, $_GET["AssemblyHistoryId"]);

	$query  = "SELECT * FROM assembly_unit_history ";
	$query .= "LEFT JOIN assembly_unit ON assembly_unit.Id = assembly_unit_history.AssemblyUnitId ";	
	$query .= "WHERE assembly_unit_history.Id = {$assemblyHistoryId}";	

	
	$result = dbRunQuery($dbLink,$query);
	
	$history = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$assembly = $r;
		$temp = array();
		$temp['Title'] = $r['Title'];
		$temp['Description'] = $r['Description'];
		$temp['SerialNumber'] = $r['SerialNumber'];
		$temp['Barcode'] = "ASU-".$r['AssemblyUnitNumber'];
		$temp['Type'] = $r['Type'];
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
		$jsonData = json_validateString($data['Data']);
		if($jsonData === false) sendResponse(null,"Data is not valid JSON");
	}
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$token = dbEscapeString($dbLink,$data["EditToken"]);

	$sqlData = array();
	$sqlData['Title'] = dbEscapeString($dbLink,$data['Title']);
	$sqlData['Description'] = dbEscapeString($dbLink,$data['Description']);
	$sqlData['Type'] = dbEscapeString($dbLink,$data['Type']);
	$sqlData['Data']['raw'] = "JSON_UNQUOTE('".dbEscapeString($dbLink,$jsonData)."')";
	$query = dbBuildUpdateQuery($dbLink,"assembly_unit_history", $sqlData, 'EditToken = "'.$token.'"');
	
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if($result == false) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["AssemblyUnitNumber"])) sendResponse(Null,"AssemblyUnitNumber not set");
	
	$jsonData = null;
	if(isset($data['Data']))
	{
		$jsonData = json_validateString($data['Data']);
		if($jsonData === false) sendResponse(null,"Data is not valid JSON");
	}
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$assemblyNo = dbEscapeString($dbLink,$data['AssemblyUnitNumber']);
	$assemblyNo = strtolower($assemblyNo);
	$assemblyNo = str_replace("asm-","",$assemblyNo);
	
	$sqlData = array();
	$sqlData['Title'] = dbEscapeString($dbLink,$data['Title']);
	$sqlData['Description'] = dbEscapeString($dbLink,$data['Description']);
	$sqlData['Type'] = dbEscapeString($dbLink,$data['Type']);
	$sqlData['Data']['raw'] = "JSON_UNQUOTE('".dbEscapeString($dbLink,$jsonData)."')";
	$sqlData['AssemblyUnitId']['raw'] = "(SELECT Id FROM assembly_unit WHERE AssemblyUnitNumber = '".$assemblyNo."' )";
	$sqlData['EditToken']['raw'] = "history_generateEditToken()";
	$query = dbBuildInsertQuery($dbLink,"assembly_unit_history", $sqlData);
	
	$query .= " SELECT EditToken FROM assembly_unit_history WHERE Id = LAST_INSERT_ID();";
	
	$error = null;
	$output = array();
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output['EditToken'] = $row[0];
				}
				mysqli_free_result($result);
			}
			if(!mysqli_more_results($dbLink)) break;
		} while (mysqli_next_result($dbLink));
	}
	else
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse($output,$error);
}
?>