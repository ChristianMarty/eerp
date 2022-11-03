<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/testing/
// Author   : Christian Marty
// Date		: 16.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["TestSystemNumber"])) sendResponse(Null,"Test System Number not set");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query  = "SELECT * FROM testSystem ";
	$query .= "LEFT JOIN testSystem_item ON testSystem.Id = testSystem_item.TestSystemId ";
	$query .= "LEFT JOIN inventory ON inventory.Id = testSystem_item.InventoryId ";
	$query .= "LEFT JOIN inventory_history ON inventory_history.Id = (SELECT Id FROM inventory_history WHERE TYPE = 'Calibration' AND InventoryId = inventory.Id ORDER BY Date DESC LIMIT 1)";
	
	
	

	
	$queryParam = array();
	$temp = dbEscapeString($dbLink, $_GET["TestSystemNumber"]);
	$temp = strtolower($temp);
	$testSystemNumber = str_replace("tsy-","",$temp);

	array_push($queryParam, "testSystem.Id = (SELECT Id FROM testSystem WHERE TestSystemNumber = '".$testSystemNumber."')");		
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);

	
	$result = dbRunQuery($dbLink,$query);
	
	$output = array();
	$output['Item'] = array();
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$temp = array();
		$temp = $r;
		$temp['InventoryNumber'] = $r['InvNo'];
		$temp['InventoryBarcode'] = "Inv-".$r['InvNo'];
		
		/*$temp['Note'] = $r['Note'];
		$temp['LocationName'] = $r['LocationName'];
		$temp['SerialNumber'] = $r['SerialNumber'];
		*/
		$output['Item'][] = $temp;
	}
	
	
	/*$query  = "SELECT * FROM assembly WHERE AssemblyNumber = ".$assemblyNumber;
	$result = dbRunQuery($dbLink,$query);
	
	$r = mysqli_fetch_assoc($result);
	$output['AssemblyNumber'] = $r['AssemblyNumber'];
	$output['AssemblyBarcode'] = "ASM-".$r['AssemblyNumber'];
	$output['Name'] = $r['Name'];
	$output['Description'] = $r['Description'];
	*/
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>