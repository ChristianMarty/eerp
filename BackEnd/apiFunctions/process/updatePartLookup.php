<?php
//*************************************************************************************************
// FileName : updatePartLookup.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

$title = "Update Part Lookup";
$description = "Upload CSV";
$parameter = '[{"Name":"Data", "Type":"data","Default":null}]';


if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	
	$data = json_decode(file_get_contents('php://input'),true);
	
	$csvDataStr = $data["Data"];
	$csvDataStr = str_replace("\r","",$csvDataStr);
	$csvDataStr = str_replace("\n","",$csvDataStr);
	
	$CSVLines = explode(";\n",$csvDataStr);
	
	$BoMData = array();
	
	foreach($CSVLines as $i => $Line)
	{
		$Line = isset($Line) ? trim($Line) : false;
		if(empty($Line)) continue;
		
		$temp  = str_getcsv($Line,";");	

		$BoMData[$i]["RefDes"] = $temp["0"];
		$BoMData[$i]["Value"] =  $temp["1"];
		$BoMData[$i]["PartNo"] = $temp["5"];
	}
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	

	dbClose($dbLink);
	
	sendResponse(null);
}


?>