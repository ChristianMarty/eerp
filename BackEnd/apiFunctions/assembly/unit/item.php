<?php
//*************************************************************************************************
// FileName : unit.php
// FilePath : apiFunctions/assembly/
// Author   : Christian Marty
// Date		: 16.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET['AssemblyUnitNumber'])) sendResponse(null,"AssemblyUnitNumber missing");
    $assemblyUnitNumber = barcodeParser_AssemblyUnitNumber($_GET["AssemblyUnitNumber"]);

	$dbLink = dbConnect();

	// Get History Data
    $query = <<<STR
        SELECT * FROM assembly_unit_history
        WHERE AssemblyUnitId = (SELECT Id FROM assembly_unit WHERE AssemblyUnitNumber = '.$assemblyUnitNumber.')
         ORDER BY Date DESC
    STR;

	$result = dbRunQuery($dbLink,$query);
	$assembly = array();
	
	$shippingProhibited = false;
	$shippingClearance = false;
	
	$history = array();
	while($r = mysqli_fetch_assoc($result)) 
	{	
		if($r['ShippingClearance'] != 0) $r['ShippingClearance'] = true;
		else $r['ShippingClearance'] = false;
		if($r['ShippingProhibited'] != 0) $r['ShippingProhibited'] = true;
		else $r['ShippingProhibited'] = false;
		
		if($r['ShippingClearance']) $shippingClearance = true;
		if($r['ShippingProhibited']) $shippingProhibited = true;

        $r['AssemblyUnitHistoryBarcode'] = "ASH-".$r['AssemblyUnitHistoryNumber'];

		$history[] = $r;
	}
	
	if($shippingProhibited) $shippingClearance = false;

    $query = <<<STR
        SELECT *,location_getName(LocationId) AS LocationName FROM assembly_unit
        LEFT JOIN assembly ON assembly.Id = assembly_unit.AssemblyId
    STR;
	
	$queryParam = array();
	$queryParam[] = " AssemblyUnitNumber = '" . $assemblyUnitNumber . "'";
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	$result = dbRunQuery($dbLink,$query);
	
	$output = array();

	$output = mysqli_fetch_assoc($result);
    $output['AssemblyBarcode'] =  "ASM-".$output['AssemblyNumber'];
    $output['AssemblyUnitBarcode'] =  "ASU-".$output['AssemblyUnitNumber'];
	$output['ShippingClearance'] =  $shippingClearance;
	$output['ShippingProhibited'] = $shippingProhibited;
	$output['History'] = $history;
	
	dbClose($dbLink);	
	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data['SerialNumber']) or !isset($data['AssemblyNumber'])) sendResponse(null,"SerialNumber or AssemblyNumber missing");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$serialNumber = dbEscapeString($dbLink,$data['SerialNumber']);
    $assemblyNumber = barcodeFormatter_AssemblyNumber($data['AssemblyNumber']);
	
	$workOrderNumber = null;
	if(isset($data['WorkOrderNumber']))
	{
		$workOrderNumber = barcodeParser_WorkOrderNumber($data['WorkOrderNumber']);
	} 
	
	
	$sqlData = array();
	$sqlData['SerialNumber'] = $serialNumber;
	if($workOrderNumber !== null)$sqlData['WorkOrderId']['raw'] = "(SELECT Id FROM workOrder WHERE WorkOrderNumber = ".$workOrderNumber.")";
	$sqlData['AssemblyId']['raw'] = "(SELECT Id FROM assembly WHERE AssemblyNumber = ".$assemblyNumber.")";
	$sqlData['AssemblyUnitNumber']['raw'] = "(SELECT generateItemNumber())";
	
	$query = dbBuildInsertQuery($dbLink,"assembly_unit", $sqlData);
	
	$query .= " SELECT AssemblyUnitNumber FROM assembly_unit WHERE Id = LAST_INSERT_ID();";

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