<?php
//*************************************************************************************************
// FileName : label.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/../config.php";

require "databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT * FROM label ";	
	
	if(isset($_GET["Id"]))
	{
		$Id = dbEscapeString($dbLink, $_GET["Id"]);
		$query.= " WHERE Id = ".$Id;		
	}
	
	$query .= " ORDER BY `Name` ASC";
	
	$result = dbRunQuery($dbLink,$query);
	
	$labels = array();
	while($r = dbGetResult($result)) 
	{
		array_push($labels, $r);
	}
	
	dbClose($dbLink);	
	sendResponse($labels);
}

?>