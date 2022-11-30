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

	$query  = "SELECT Test.Type AS Test, Inspection.Type AS Inspection, AssemblyUnitNumber, Note, SerialNumber, location_getName(LocationId) AS LocationName, ShippingProhibited.ShippingProhibited, ShippingClearance.ShippingClearance FROM assembly_unit ";
	$query .= "LEFT JOIN assembly_unit_history AS Test ON Test.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND Type IN('Test Fail','Test Pass') ORDER BY Data DESC LIMIT 1) ";
	$query .= "LEFT JOIN assembly_unit_history AS Inspection ON Inspection.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND Type IN('Inspection Fail','Inspection Pass') ORDER BY Data DESC LIMIT 1) "; 
	$query .= "LEFT JOIN assembly_unit_history AS ShippingProhibited ON ShippingProhibited.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND ShippingProhibited = 1) "; 
	$query .= "LEFT JOIN assembly_unit_history AS ShippingClearance ON ShippingClearance.Id = (SELECT Id FROM assembly_unit_history WHERE assembly_unit.Id = assembly_unit_history.AssemblyUnitId AND ShippingClearance = 1) "; 
	
	$queryParam = array();
	$temp = dbEscapeString($dbLink, $_GET["AssemblyNumber"]);
	$temp = strtolower($temp);
	$assemblyNumber = str_replace("asm-","",$temp);

	array_push($queryParam, "AssemblyId = (SELECT Id FROM assembly WHERE AssemblyNumber = '".$assemblyNumber."')");		
	
	if(isset($_GET['SerialNumber']))
	{
		$serialNumber = dbEscapeString($dbLink, $_GET['SerialNumber']);
		array_push($queryParam, "SerialNumber = '".$serialNumber."'");	
	}
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	$query .= " ORDER BY assembly_unit.SerialNumber ASC";

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
		
		
		if($r['ShippingClearance'] != 0) $temp['ShippingClearance'] = true;
		else $temp['ShippingClearance'] = false;
		if($r['ShippingProhibited'] != 0) $temp['ShippingProhibited'] = true;
		else $temp['ShippingProhibited'] = false;
		
		if($temp['ShippingProhibited'] == true) $temp['ShippingClearance'] = false;
		
		if($r['Test'] == 'Test Pass') $temp['LastTestPass'] = true;
		else if($r['Test'] == 'Test Fail') $temp['LastTestPass'] = false;
		else $temp['LastTestPass'] = null;
		
		if($r['Inspection'] == 'Inspection Pass') $temp['LastInspectionPass'] = true;
		else if($r['Inspection'] == 'Inspection Fail') $temp['LastInspectionPass'] = false;
		else $temp['LastInspectionPass'] = null;
		
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

