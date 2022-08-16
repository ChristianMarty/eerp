<?php
//*************************************************************************************************
// FileName : add.php
// FilePath : apiFunctions/assembly/history
// Author   : Christian Marty
// Date		: 16.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data["AssemblyNo"])) sendResponse(Null,"AssemblyNo not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	
	
	$assemblyNo = dbEscapeString($dbLink,$data['AssemblyNo']);
	$assemblyNo = strtolower($assemblyNo);
	$assemblyNo = str_replace("asm-","",$assemblyNo);
	
	$sqlData = array();
	$sqlData['Title'] = dbEscapeString($dbLink,$data['Title']);
	$sqlData['Description'] = dbEscapeString($dbLink,$data['Description']);
	$sqlData['Data'] = dbEscapeString($dbLink,$data['Data']);
	$sqlData['AssemblyId']['raw'] = "(SELECT Id FROM assembly WHERE AssemblyNo = '".$assemblyNo."' )";
	$query = dbBuildInsertQuery($dbLink,"assembly_history", $sqlData);
	
	$result = dbRunQuery($dbLink,$query);
	
	$error = null;
	if($result == false) $error = "Error description: " . mysqli_error($dbLink);
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}

?>