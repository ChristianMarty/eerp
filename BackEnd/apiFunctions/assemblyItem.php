<?php
//*************************************************************************************************
// FileName : assemblyItem.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 16.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require __DIR__ . "/../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query  = "SELECT * FROM assembly_item ";
	$query .= "LEFT JOIN assembly ON assembly.Id = assembly_item.AssemblyId ";
	
	if(isset($_GET["SerialNumber"]))
	{
		$sn = dbEscapeString($dbLink, $_GET["SerialNumber"]);
		$query .= "WHERE SerialNumber LIKE '".$sn."'";
	}
	
	$queryParam = array();

	$query = dbBuildQuery($dbLink, $query, $queryParam);
	
	$result = dbRunQuery($dbLink,$query);
	
	$assembly = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['AssemblyBarcode'] = "ASM-".$r['AssemblyNo'];
		$r['AssemblyItemBarcode'] = "ASI-".$r['AssemblyItemNo'];
		$assembly[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($assembly);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data['SerialNumber']) or !isset($data['AssemblyNumber'])) sendResponse($output,"SerialNumber or  AssemblyNumber missing");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$serialNumber = dbEscapeString($dbLink,$data['SerialNumber']);
	
	$assemblyNumber = dbEscapeString($dbLink,$data['AssemblyNumber']);
	$assemblyNumber = strtolower($assemblyNumber);
	$assemblyNumber = str_replace("asm-","",$assemblyNumber);
	
	$workOrderNumber = null;
	if(isset($data['WorkOrderNumber']))
	{
		$workOrderNumber = dbEscapeString($dbLink,$data['WorkOrderNumber']);
		$workOrderNumber = strtolower($workOrderNumber);
		$workOrderNumber = str_replace("wo-","",$workOrderNumber);
	} 
	
	
	$sqlData = array();
	$sqlData['SerialNumber'] = $serialNumber;
	$sqlData['WorkOrderId']['raw'] = "(SELECT Id FROM workOrder WHERE WorkOrderNo = ".$workOrderNumber.")";
	$sqlData['AssemblyId']['raw'] = "(SELECT Id FROM assembly WHERE AssemblyNo = ".$assemblyNumber.")";
	$sqlData['AssemblyItemNo']['raw'] = "(SELECT generateItemNumber())";
	
	$query = dbBuildInsertQuery($dbLink,"assembly_item", $sqlData);
	
	$query .= " SELECT AssemblyItemNo FROM assembly_item WHERE Id = LAST_INSERT_ID();";

	$error = null;
	$output = null;
	
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output = $row[0];
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