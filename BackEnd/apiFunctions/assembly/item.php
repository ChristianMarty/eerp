<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/assembly/
// Author   : Christian Marty
// Date		: 16.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require __DIR__ . "/../../config.php";


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["AssemblyItemNo"])) sendResponse(Null,"AssemblyItemNo not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query  = "SELECT *, assembly_item_history.Id AS HistoryItemId, location_getName(LocationId) AS LocationName FROM assembly ";	
	$query .= "LEFT JOIN assembly_item ON  assembly.Id = assembly_item.AssemblyId ";
	$query .= "LEFT JOIN assembly_item_history ON  assembly_item.Id = assembly_item_history.AssemblyItemId ";

	$queryParam = array();
	$temp = dbEscapeString($dbLink, $_GET["AssemblyItemNo"]);
	$temp = strtolower($temp);
	$assemblyItemNo = str_replace("asi-","",$temp);

	array_push($queryParam, "AssemblyItemNo LIKE '".$assemblyItemNo."'");		
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	$query .= " ORDER BY assembly_item_history.Date DESC";
	
	
	$result = dbRunQuery($dbLink,$query);
	
	$assembly = array();
	$history = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$assembly = $r;
		$temp = array();
		$temp['Title'] = $r['Title'];
		$temp['Description'] = $r['Description'];
		//$temp['Data'] = $r['Data'];
		$temp['EditToken'] = $r['EditToken'];
		$temp['Date'] = $r['Date'];
		$temp['Id'] = intval($r['HistoryItemId']);
		
		$history[] = $temp;
	}
	
	$output = array();
	$output['AssemblyBarcode'] = "ASM-".$assembly['AssemblyNo'];
	$output['AssemblyItemBarcode'] = "ASI-".$assembly['AssemblyItemNo'];
		
	$output['AssemblyNo'] = $assembly['AssemblyNo'];
	$output['AssemblyItemNo'] = $assembly['AssemblyItemNo'];
	$output['Name'] = $assembly['Name'];
	$output['Description'] = $assembly['Description'];
	$output['SerialNumber'] = $assembly['SerialNumber'];
	$output['LocationName'] = $assembly['LocationName'];
	
	$output['History'] = $history;
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>