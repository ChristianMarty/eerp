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
	if(!isset($_GET["AssemblyNumber"])) sendResponse(Null,"Assembly Number not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query  = "SELECT AssemblyUnitNumber, Note, SerialNumber, location_getName(LocationId) AS LocationName FROM assembly_unit ";

	$queryParam = array();
	$temp = dbEscapeString($dbLink, $_GET["AssemblyNumber"]);
	$temp = strtolower($temp);
	$assemblyNumber = str_replace("asm-","",$temp);

	array_push($queryParam, "AssemblyId = (SELECT Id FROM assembly WHERE AssemblyNumber = '".$assemblyNumber."')");		
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	$query .= " ORDER BY assembly_unit.SerialNumber DESC";
	
	$result = dbRunQuery($dbLink,$query);
	
	$output = array();
	$output['Unit'] = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$temp = array();
		$temp['AssemblyUnitNumber'] = $r['AssemblyUnitNumber'];
		$temp['AssemblyUnitBarcode'] = "ASU-".$r['AssemblyUnitNumber'];
		$temp['Note'] = $r['Note'];
		$temp['LocationName'] = $r['LocationName'];
		$temp['SerialNumber'] = $r['SerialNumber'];
		
		$output['Unit'][] = $temp;
	}
	
	
	$query  = "SELECT * FROM assembly WHERE AssemblyNumber = ".$assemblyNumber;
	$result = dbRunQuery($dbLink,$query);
	
	$r = mysqli_fetch_assoc($result);
	$output['AssemblyNumber'] = $r['AssemblyNumber'];
	$output['AssemblyBarcode'] = "ASM-".$r['AssemblyNumber'];
	$output['Name'] = $r['Name'];
	$output['Description'] = $r['Description'];
	
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>