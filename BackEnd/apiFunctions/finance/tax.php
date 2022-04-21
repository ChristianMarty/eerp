<?php
//*************************************************************************************************
// FileName : tax.php
// FilePath : apiFunctions/finance/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	if(!isset($_GET["Type"]))$type = dbEscapeString($dbLink,$_GET["Type"]);
	
	$query = "SELECT * FROM finance_tax ";
	
	if(isset($type))
	{
		$query .= "WHERE Type = '".$type."'";
	}
	
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	while($r = mysqli_fetch_assoc($result))
	{
		$r['Id'] = intval($r['Id']);
		array_push($output, $r);
	}

	dbClose($dbLink);	
	sendResponse($output);
}
?>