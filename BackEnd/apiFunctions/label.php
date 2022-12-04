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

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT * FROM label ";	
	
	$param = array();
	if(isset($_GET["Id"])) $param[] = "Id = " . dbEscapeString($dbLink, $_GET["Id"]);
	if(isset($_GET["Tag"])) $param[] = "Tag = '" . dbEscapeString($dbLink, $_GET["Tag"]) . "'";
	
	$query = dbBuildQuery($dbLink, $query, $param);
	$query .= " ORDER BY `Name` ASC";
	
	$result = dbRunQuery($dbLink,$query);
	
	$labels = array();
	while($r = dbGetResult($result)) 
	{
		$labels[] = $r;
	}
	
	dbClose($dbLink);	
	sendResponse($labels);
}

?>