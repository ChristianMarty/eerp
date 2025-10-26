<?php
//*************************************************************************************************
// FileName : altium_pickAndPlace.php
// FilePath : apiFunctions/project/analyze
// Author   : Christian Marty
// Date		: 07.08.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;


$title = "Altium Pick & Place";
$description = "";

// TODO: This is not working -> fix  it all


if($api->isPost(\Permission::BillOfMaterial_Create))
{
    $data = $api->getPostData();

    if(!isset($data->csv)) $api->returnParameterMissingError("csv");
    $csvDataStr = str_replace("\r","",$data->csv);
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

	$api->returnData($BoMData);
}
