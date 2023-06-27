<?php
//*************************************************************************************************
// FileName : placement.php
// FilePath : apiFunctions/project/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["ProjectId"])) sendResponse(NULL, "Project Id Undefined");

	$dbLink = dbConnect();
	if($dbLink == null) return null;

	
	$projectId = dbEscapeString($dbLink, $_GET["ProjectId"]);
	
	$query = "SELECT * FROM project_bom ";
	$query .= "WHERE ProjectId = ".$projectId;
	

	$bom = array();
	

	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		array_push($bom, $r);
	}
	
	dbClose($dbLink);
	
	sendResponse($bom);
}

?>