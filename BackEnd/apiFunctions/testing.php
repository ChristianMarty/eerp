<?php
//*************************************************************************************************
// FileName : testing.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT TestSystemNumber, Name, Description  FROM testSystem ";

	$result = dbRunQuery($dbLink,$query);
	$output = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['TestSystemBarcode'] = "TSY-".$r['TestSystemNumber'];
		array_push($output, $r);
	}

	dbClose($dbLink);	
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data['Name'])) sendResponse($output,"Name missing");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$name = dbEscapeString($dbLink,$data['Name']);
	$description = dbEscapeString($dbLink,$data['Description']);
	
	
	$sqlData = array();
	$sqlData['Name'] = $name;
	$sqlData['Description']  = $description;
	$sqlData['TestSystemNumber']['raw'] = "(SELECT generateItemNumber())";
	
	$query = dbBuildInsertQuery($dbLink,"testSystem", $sqlData);
	
	$query .= " SELECT TestSystemNumber FROM testSystem WHERE Id = LAST_INSERT_ID();";

	$error = null;
	$output = null;
	
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output = "TSY-".$row[0];
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
	sendResponse($output, $error);
}


?>
