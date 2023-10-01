<?php
//*************************************************************************************************
// FileName : altium_pickAndPlace.php
// FilePath : apiFunctions/project/analyze
// Author   : Christian Marty
// Date		: 07.08.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";


$title = "Altium Pick & Place";
$description = "";

// TODO: This is not working -> fix  it all


if($_SERVER['REQUEST_METHOD'] == 'POST')
{

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$output = array();
	$bom = array();
	
	$PriceTotal = 0;
	
	$data = json_decode(file_get_contents('php://input'),true);
	$csvDataStr =   $data["csv"];
	$csvDataStr = str_replace("\r","",$csvDataStr);

	$CSVLines = explode("\n",$csvDataStr);
	
	$BoMData = array();
	
	foreach($CSVLines as $i => $Line)
	{
		$Line = isset($Line) ? trim($Line) : false;
		if(empty($Line)) continue;
		
		$temp  = str_getcsv($Line,',','"');	

		$BoMData[$i]["ReferenceDesignator"] = $temp["2"];
		$BoMData[$i]["Description"] =  $temp["1"];
		$BoMData[$i]["XPosition"] = $temp["4"];
		$BoMData[$i]["YPosition"] = $temp["5"];
		$BoMData[$i]["Rotation"] = $temp["6"];
		
		if($temp["3"] == "TopLayer") $BoMData[$i]["Layer"]  = "Top";
		else if($temp["3"] == "BottomLayer") $BoMData[$i]["Layer"]  = "Bottom";
		else $BoMData[$i]["Layer"]  = "Other";
		
		$BoMData[$i]["ProductionPartBarcode"] = "GCT-".$temp["0"];
	}

	dbClose($dbLink);	
	sendResponse($BoMData);
}

?>